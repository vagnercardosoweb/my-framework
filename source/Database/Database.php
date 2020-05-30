<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

namespace Core\Database;

use Core\Database\Connection\MySqlConnection;
use Core\Database\Connection\PostgreSqlConnection;
use Core\Database\Connection\SQLiteConnection;
use Core\Database\Connection\SqlServerConnection;
use Core\Database\Connection\Statement;
use Core\Event;
use Core\Helpers\Helper;
use Core\Helpers\Obj;

/**
 * Class Database.
 *
 * @method \PDO beginTransaction()
 * @method \PDO commit()
 * @method \PDO errorCode()
 * @method \PDO errorInfo()
 * @method \PDO exec(string $statement)
 * @method \PDO getAttribute(int $attribute)
 * @method \PDO getAvailableDrivers()
 * @method \PDO inTransaction()
 * @method \PDO lastInsertId(string $name = null)
 * @method \Core\Database\Connection\Statement prepare(string $statement, array $driver_options = array())
 * @method \PDO quote(string $string, int $parameter_type = \PDO::PARAM_STR)
 * @method \PDO rollBack()
 * @method \PDO setAttribute(int $attribute, mixed $value)
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Database
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var string
     */
    protected $defaultDriver = 'mysql';

    /**
     * @param string $method
     * @param mixed ...$arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        if ($this->pdo instanceof \PDO && method_exists($this->pdo, $method)) {
            return $this->pdo->{$method}(...$arguments);
        }

        throw new \BadMethodCallException(
            sprintf('Call to undefined method %s::%s()', get_class(), $method)
        );
    }

    /**
     * @param string $driver
     * @param array $config
     *
     * @return $this
     */
    public function addConnection(string $driver, array $config): self
    {
        $this->connections[$driver] = $config;

        return $this;
    }

    /**
     * @param string $driver
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function driver(?string $driver = null): Database
    {
        return $this->connection($driver);
    }

    /**
     * @param string|null $driver
     *
     * @throws \Exception
     *
     * @return \Core\Database\Database
     */
    public function connection(?string $driver = null): Database
    {
        $driver = $driver ?? $this->getDefaultDriver();

        if (empty($config = $this->connections[$driver])) {
            throw new \Exception(
                "Database connections {$driver} ".
                'does not exist configured.'
            );
        }

        if ($config instanceof \PDO) {
            $this->pdo = $config;

            return $this;
        }

        $config['driver'] = $config['driver'] ?? $driver;

        if (!$this->connections[$driver] instanceof \PDO) {
            if ('pgsql' == $config['driver']) {
                $this->connections[$driver] = (new PostgreSqlConnection($config));
            } else if ('sqlsrv' == $config['driver']) {
                $this->connections[$driver] = (new SqlServerConnection($config));
            } else if ('sqlite' == $config['driver']) {
                $this->connections[$driver] = (new SQLiteConnection($config));
            } else {
                $this->connections[$driver] = (new MySqlConnection($config));
            }
        }

        $this->pdo = $this->connections[$driver];

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
    }

    /**
     * @param string $defaultDriver
     *
     * @return \Core\Database\Database
     */
    public function setDefaultDriver(string $defaultDriver): self
    {
        $this->defaultDriver = $defaultDriver;

        return $this;
    }

    /**
     * @param \Closure $callback
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function transaction(\Closure $callback)
    {
        $injectThis = $this;

        if (func_num_args() >= 2) {
            $injectThis = func_get_args()[1];
        }

        $invokeCallback = function () use ($callback, $injectThis) {
            return call_user_func_array($callback, [$injectThis, $this]);
        };

        if ($this->inTransaction()) {
            return $invokeCallback();
        }

        try {
            $this->beginTransaction();
            $result = $invokeCallback();
            $this->commit();

            return $result;
        } catch (\Exception $e) {
            $this->rollBack();

            throw $e;
        }
    }

    /**
     * @param string $table
     * @param array|object $data
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function create(string $table, $data): ?int
    {
        $data = Obj::fromArray($data);
        $data = $bindings = ($this->event("{$table}:creating", $data) ?: $data);
        $values = '(:'.implode(', :', array_keys(get_object_vars($data))).')';
        $columns = implode(', ', array_keys(get_object_vars($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
        $lastInsertId = $this->query($sql, $bindings)->lastInsertId();

        $this->event("{$table}:created", $lastInsertId);

        return !empty($lastInsertId)
            ? (int) $lastInsertId
            : null;
    }

    /**
     * @param string $sql
     * @param string|array $bindings
     * @param array $driverOptions
     *
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function query(string $sql, $bindings = null, array $driverOptions = []): Statement
    {
        if (empty($sql)) {
            throw new \InvalidArgumentException(
                'Parameter $sql can not be empty.'
            );
        }

        // Execute sql
        $statement = $this->prepare($sql, $driverOptions);
        $statement->bindValues($bindings);
        $statement->execute();

        return $statement;
    }

    /**
     * @param string $table
     * @param array|object $data
     * @param string $condition
     * @param array|string $bindings
     *
     * @throws \Exception
     *
     * @return object[]|null
     */
    public function update(string $table, $data, string $condition, $bindings = null): ?array
    {
        if (!$rows = $this->findAndTransformRowsObject($table, $condition, $bindings)) {
            return null;
        }

        $set = [];
        $data = Obj::fromArray($data);
        $data = ($this->event("{$table}:updating", $data, $rows) ?: $data);

        Helper::parseStr($bindings, $bindings);

        foreach ($data as $key => $value) {
            $binding = $key;

            foreach ($rows as &$row) {
                $row->{$key} = $value;
            }

            $set[] = "{$key} = :{$binding}";
            $bindings[$binding] = $value;
        }

        $statement = sprintf("UPDATE {$table} SET %s {$condition}", implode(', ', $set));
        $this->query($statement, $bindings);
        $this->event("{$table}:updated", $rows);

        return $rows;
    }

    /**
     * @param string $table
     * @param string $condition
     * @param string|array $bindings
     *
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function read(string $table, ?string $condition = null, $bindings = null): Statement
    {
        return $this->query("SELECT {$table}.* FROM {$table} {$condition}", $bindings);
    }

    /**
     * @param string $table
     * @param string $condition
     * @param string|array $bindings
     *
     * @throws \Exception
     *
     * @return object[]|null
     */
    public function delete(string $table, string $condition, $bindings = null): ?array
    {
        if (!$rows = $this->findAndTransformRowsObject($table, $condition, $bindings)) {
            return null;
        }

        $this->event("{$table}:deleting", $rows);
        $this->query("DELETE {$table} FROM {$table} {$condition}", $bindings);
        $this->event("{$table}:deleted", $rows);

        return $rows;
    }

    /**
     * @param string|null $name
     *
     * @return mixed
     */
    private function event(?string $name = null)
    {
        $event = Event::getInstance();

        if (!empty($name)) {
            return $event->emit(
                $name,
                ...array_slice(func_get_args(), 1)
            );
        }

        return false;
    }

    /**
     * @param string       $table
     * @param string       $condition
     * @param array|string $bindings
     *
     * @throws \Exception
     *
     * @return object[]
     */
    private function findAndTransformRowsObject(string $table, string $condition, $bindings = null)
    {
        $rows = $this->read($table, $condition, $bindings)->fetchAll();

        foreach ($rows as $key => $row) {
            $rows[$key] = Obj::fromArray($row);
        }

        return $rows;
    }
}
