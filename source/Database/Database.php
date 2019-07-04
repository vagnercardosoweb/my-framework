<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Database;

use Core\App;
use Core\Database\Connection\Statement;
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
     * @var \Core\Database\Connect
     */
    private $connect;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @param \Core\Database\Connect $connect
     *
     * @throws \Exception
     */
    public function __construct(Connect $connect)
    {
        $this->connect = $connect;
        $this->pdo = $this->connect->getPdo();
    }

    /**
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        if ($this->pdo instanceof \PDO && method_exists($this->pdo, $method)) {
            return $this->pdo->{$method}(...$arguments);
        }

        throw new \BadMethodCallException(
            sprintf('Call to undefined method %s::%s()', get_class(), $method), E_USER_ERROR
        );
    }

    /**
     * @param string $driver mysql|pgsql|sqlite|sqlsrv
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function driver(?string $driver = null): Database
    {
        if (empty($driver)) {
            $driver = $this->connect->getDefaultConnection();
        }

        if (empty($this->connections[$driver])) {
            $this->connections[$driver] = $this->connect->connection($driver);
        }

        return $this->connections[$driver];
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
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
        try {
            $this->beginTransaction();
            $callback = $callback($this);
            $this->commit();

            return $callback;
        } catch (\Exception $e) {
            $this->rollBack();

            throw $e;
        }
    }

    /**
     * @param string       $table
     * @param array|object $data
     *
     * @throws \Exception
     *
     * @return int|null
     */
    public function create(string $table, $data): ?int
    {
        $data = Obj::fromArray($data);
        $data = $bindings = ($this->emitEvent("{$table}:creating", $data) ?: $data);
        $values = '(:'.implode(', :', array_keys(get_object_vars($data))).')';
        $columns = implode(', ', array_keys(get_object_vars($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
        $lastInsertId = $this->query($sql, $bindings)->lastInsertId();

        $this->emitEvent("{$table}:created", $lastInsertId);

        return !empty($lastInsertId)
            ? (int)$lastInsertId
            : null;
    }

    /**
     * @param string       $sql
     * @param string|array $bindings
     * @param array        $driverOptions
     *
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function query(string $sql, $bindings = null, array $driverOptions = []): Statement
    {
        try {
            if (empty($sql)) {
                throw new \InvalidArgumentException(
                    'Parameter $sql can not be empty.', E_ERROR
                );
            }

            // Execute sql
            $statement = $this->prepare($sql, $driverOptions);
            $statement->bindValues($bindings);
            $statement->execute();

            return $statement;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param string       $table
     * @param array|object $data
     * @param string       $condition
     * @param array|string $bindings
     *
     * @throws \Exception
     *
     * @return object|null
     */
    public function update(string $table, $data, string $condition, $bindings = null): ?object
    {
        Helper::parseStr($bindings, $bindings);

        $updated = $this->read($table, $condition, $bindings)->fetch();
        $updated = Obj::fromArray($updated);

        if (empty(get_object_vars($updated))) {
            return null;
        }

        $set = [];
        $data = Obj::fromArray($data);
        $data = ($this->emitEvent("{$table}:updating", $data) ?: $data);

        foreach ($data as $key => $value) {
            $value = filter_var($value, FILTER_DEFAULT);
            $updated->{$key} = $value;

            if (!empty($bindings[$key])) {
                $key = sprintf("{$key}_%s", mt_rand(1, time()));
            }

            $set[] = "{$key} = :{$key}";
            $bindings[$key] = $value;
        }

        $statement = sprintf("UPDATE {$table} SET %s {$condition}", implode(', ', $set));
        $this->query($statement, $bindings);

        $this->emitEvent("{$table}:updated", $updated);

        return $updated;
    }

    /**
     * @param string       $table
     * @param string       $condition
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
     * @param string       $table
     * @param string       $condition
     * @param string|array $bindings
     *
     * @throws \Exception
     *
     * @return object
     */
    public function delete($table, $condition, $bindings = null): ?object
    {
        $table = (string)$table;
        $condition = (string)$condition;

        $deleted = $this->read($table, $condition, $bindings)->fetch();
        $deleted = Obj::fromArray($deleted);

        if (empty(get_object_vars($deleted))) {
            return null;
        }

        $this->emitEvent("{$table}:deleting", $deleted);

        $statement = "DELETE FROM {$table} {$condition}";
        $this->query($statement, $bindings);

        $this->emitEvent("{$table}:deleted", $deleted);

        return $deleted;
    }

    /**
     * @param string|null $name
     * @param mixed ... (Opcional) Argumento(s)
     *
     * @return mixed
     */
    private function emitEvent(?string $name = null)
    {
        $event = App::getInstance()
            ->resolve('event')
        ;

        if (!empty($name) && $event) {
            $arguments = func_get_args();
            array_shift($arguments);

            return $event->emit(
                (string)$name, ...$arguments
            );
        }

        return false;
    }
}
