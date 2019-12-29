<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
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
     * @param \Core\Database\Connect $connect
     *
     * @throws \Exception
     */
    public function __construct(Connect $connect)
    {
        $this->connect = $connect;
    }

    /**
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @return mixed
     */
    public function __call(string $method, $arguments)
    {
        if ($this->getPdo() instanceof \PDO && method_exists($this->getPdo(), $method)) {
            return $this->getPdo()->{$method}(...$arguments);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class(), $method), E_USER_ERROR);
    }

    /**
     * @param string $driver
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function driver(?string $driver = null): Database
    {
        return $this->connect->connection($driver);
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->connect->getPdo();
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
        $param = $this;

        if (func_num_args() >= 2) {
            $param = func_get_args()[1];
        }

        if ($this->inTransaction()) {
            return $callback($param);
        }

        try {
            $this->beginTransaction();
            $callback = $callback($param);
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
        $data = $bindings = ($this->event("{$table}:creating", $data) ?: $data);
        $values = '(:'.implode(', :', array_keys(get_object_vars($data))).')';
        $columns = implode(', ', array_keys(get_object_vars($data)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
        $lastInsertId = $this->query($sql, $bindings)->lastInsertId();

        $this->event("{$table}:created", $lastInsertId);

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
                throw new \InvalidArgumentException('Parameter $sql can not be empty.', E_ERROR);
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
        $data = ($this->event("{$table}:updating", $data) ?: $data);

        foreach ($data as $key => $value) {
            $binding = $key;
            $updated->{$key} = $value;

            if (!empty($bindings[$binding])) {
                $binding = sprintf("{$binding}_%s", mt_rand(1, time()));
            }

            $set[] = "{$key} = :{$binding}";
            $bindings[$binding] = $value;
        }

        $statement = sprintf("UPDATE {$table} SET %s {$condition}", implode(', ', $set));
        $this->query($statement, $bindings);
        $this->event("{$table}:updated", $updated);

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
     * @return array[object]|null
     */
    public function delete(string $table, string $condition, $bindings = null): ?array
    {
        $rows = $this->read($table, $condition, $bindings)->fetchAll();

        if (empty($rows[0])) {
            return null;
        }

        foreach ($rows as $key => $deleted) {
            $rows[$key] = Obj::fromArray($deleted);
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
        $event = App::getInstance()->resolve('event');

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
