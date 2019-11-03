<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 03/11/2019 Vagner Cardoso
 */

namespace Core\Database;

use Core\App;
use Core\Database\Connection\Statement;
use Core\Helpers\Helper;
use Core\Helpers\Obj;

/**
 * Class Model.
 *
 * @property \Slim\Collection             $settings
 * @property \Slim\Http\Environment       $environment
 * @property \Slim\Http\Request           $request
 * @property \Slim\Http\Response          $response
 * @property \Slim\Router                 $router
 * @property \Core\View                   $view
 * @property \Core\Session\Session|object $session
 * @property \Core\Session\Flash|object   $flash
 * @property \Core\Mailer\Mailer          $mailer
 * @property \Core\Password\Password      $hash
 * @property \Core\Encryption             $encryption
 * @property \Core\Jwt                    $jwt
 * @property \Core\Logger                 $logger
 * @property \Core\Event                  $event
 * @property \Core\Database\Database      $db
 * @property \Core\Database\Connect       $connect
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model implements \ArrayAccess
{
    /**
     * @var string
     */
    protected $driver;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var int
     */
    protected $fetchStyle;

    /**
     * @var \Core\Database\Connection\Statement
     */
    protected $statement;

    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $join = [];

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $group = [];

    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var object
     */
    protected $data;

    /**
     * @var array
     */
    protected $reset = [];

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($provider = App::getInstance()
            ->resolve($name)) {
            return $provider;
        }

        if (isset($this->data->{$name})) {
            return $this->data->{$name};
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($name, $value): void
    {
        $this->data = Obj::fromArray($this->data);
        $this->data->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->data->{$name});
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset($name): void
    {
        unset($this->data->{$name});
    }

    /**
     * @param mixed $name
     *
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->__get($name);
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($name, $value): void
    {
        $this->__set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function offsetUnset($name): void
    {
        $this->__unset($name);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Obj::toArray($this->data);
    }

    /**
     * @return object
     */
    public function toObject(): object
    {
        return Obj::fromArray($this->data);
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function rowCount(): int
    {
        return $this->buildSqlStatement()->rowCount();
    }

    /**
     * @param array $properties
     * @param bool  $reset
     *
     * @return self
     */
    public function clear(array $properties = [], bool $reset = false): self
    {
        $notReset = array_diff(['table', 'primaryKey', 'driver', 'fetchStyle', 'statement', 'data'], $properties);
        $reflection = new \ReflectionClass(get_class($this));

        foreach ($reflection->getProperties() as $property) {
            if (!in_array($property->getName(), $notReset)) {
                if (empty($properties) || in_array($property->getName(), $properties)) {
                    if ($reset) {
                        $this->reset[$property->getName()] = true;
                    } else {
                        $value = preg_match('/@var\s+(array)/im', $property->getDocComment()) ? [] : null;
                        $this->{$property->getName()} = $value;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string|array|null $properties
     *
     * @return self
     */
    public function reset(array $properties = []): self
    {
        return $this->clear($properties, true);
    }

    /**
     * @param string $column
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count($column = '1'): int
    {
        return (int)$this->select("COUNT({$column}) AS count")
            ->order('count DESC')->limit(1)
            ->buildSqlStatement()
            ->fetch(\PDO::FETCH_OBJ)
            ->count;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return self
     */
    public function limit($limit, $offset = 0): self
    {
        if (is_numeric($limit)) {
            $this->limit = (int)$limit;

            if (is_numeric($offset)) {
                $this->offset($offset);
            }
        }

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return self
     */
    public function offset($offset): self
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * @param string|array|null $order
     *
     * @return self
     */
    public function order($order): self
    {
        $this->mountProperty($order, 'order');

        return $this;
    }

    /**
     * @param mixed $select
     *
     * @return self
     */
    public function select($select = '*'): self
    {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        $this->mountProperty($select, 'select');

        return $this;
    }

    /**
     * @param string $table
     *
     * @return self|string
     */
    public function table($table = null)
    {
        if (!empty($table)) {
            $this->table = (string)$table;

            return $this;
        }

        return $this->table;
    }

    /**
     * @param string|array|null $join
     *
     * @return self
     */
    public function join($join): self
    {
        $this->mountProperty($join, 'join');

        return $this;
    }

    /**
     * @param string|array|null $group
     *
     * @return self
     */
    public function group($group): self
    {
        $this->mountProperty($group, 'group');

        return $this;
    }

    /**
     * @param string|array|null $having
     *
     * @return self
     */
    public function having($having): self
    {
        $this->mountProperty($having, 'having');

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
        /** @var \Core\Database\Database $db */
        $db = $this->db->driver($this->driver);

        try {
            $db->beginTransaction();
            $callback = $callback($this);
            $db->commit();

            return $callback;
        } catch (\Exception $e) {
            $db->rollBack();

            throw $e;
        }
    }

    /**
     * @param array|object|null $data
     * @param bool              $validate
     *
     * @throws \Exception
     *
     * @return self
     */
    public function save($data = null, bool $validate = true): self
    {
        if (is_array($data) || is_object($data)) {
            $this->data($data, $validate);
        }

        $where = $this->where;
        $bindings = $this->bindings;
        $exists = $this->fetchById($this->getPrimaryValue());

        if ($exists || !empty($where)) {
            $this->where = $where;
            $this->bindings = $bindings;

            return $this->update($data, $validate);
        }

        return $this->create($data, $validate);
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @return self
     */
    public function data($data, bool $validate = true): self
    {
        $data = array_merge(
            Obj::toArray($this->data),
            Obj::toArray($data)
        );

        if (method_exists($this, '_data')) {
            $this->_data($data, $validate);
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * @param int|array $id
     * @param int       $fetchStyle
     *
     * @throws \Exception
     *
     * @return self[]|self|null
     */
    public function fetchById($id, $fetchStyle = null): ?self
    {
        if (!empty($id) && $this->primaryKey) {
            if (is_array($id)) {
                $this->where(sprintf(
                    "AND {$this->table}.{$this->primaryKey} IN (%s)",
                    implode(',', $id)
                ));

                return $this->fetchAll($fetchStyle);
            }

            $this->where("AND {$this->table}.{$this->primaryKey} = :pkey", [
                'pkey' => filter_params($id)[0],
            ]);
        }

        if (empty($this->where)) {
            return null;
        }

        return $this->fetch($fetchStyle);
    }

    /**
     * @param string|array $where
     * @param string|array $bindings
     *
     * @return self
     */
    public function where($where, $bindings = null): self
    {
        $this->mountProperty($where, 'where');
        $this->bindings($bindings);

        return $this;
    }

    /**
     * @param string|array $bindings
     *
     * @return self
     */
    public function bindings($bindings): self
    {
        Helper::parseStr($bindings, $this->bindings);

        return $this;
    }

    /**
     * @param int   $fetchStyle
     * @param mixed $fetchArgument
     *
     * @throws \Exception
     *
     * @return self[]|null
     */
    public function fetchAll($fetchStyle = null, $fetchArgument = null)
    {
        if (empty($fetchStyle) && $this->fetchStyle) {
            $fetchStyle = $this->fetchStyle;
        }

        $this->buildSqlStatement();

        if ($this->statement->isFetchObject($fetchStyle)) {
            $fetchStyle = \PDO::FETCH_CLASS;
            $fetchArgument = get_called_class();
        }

        $rows = $this->statement->fetchAll($fetchStyle, $fetchArgument);

        if (!empty($rows)) {
            foreach ($rows as $index => $row) {
                if (method_exists($this, '_row')) {
                    $this->_row($row);
                }

                $rows[$index] = $row;
            }
        }

        return $rows ?: null;
    }

    /**
     * @param int $fetchStyle
     *
     * @throws \Exception
     *
     * @return self|null
     */
    public function fetch($fetchStyle = null): ?self
    {
        if (empty($fetchStyle) && $this->fetchStyle) {
            $fetchStyle = $this->fetchStyle;
        }

        $this->buildSqlStatement();

        if ($this->statement->isFetchObject($fetchStyle)) {
            $fetchStyle = get_called_class();
        }

        $row = $this->statement->fetch($fetchStyle);

        if (!empty($row)) {
            if (method_exists($this, '_row')) {
                $this->_row($row);
            }
        }

        return $row ?: null;
    }

    /**
     * @return string|null
     */
    public function getPrimaryValue(): ?string
    {
        return $this->{$this->getPrimaryKey()} ?? null;
    }

    /**
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey;
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return self
     */
    public function update($data, bool $validate = true): self
    {
        if (is_array($data) || is_object($data)) {
            $this->data($data, $validate);
        }

        $this->checkWherePk();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[update] `%s::where()` is empty.', get_called_class()),
                E_USER_ERROR
            );
        }

        $this->data = $this->db
            ->driver($this->driver)
            ->update(
                $this->table,
                $this->data,
                "WHERE {$this->normalizeProperty($this->where)}",
                $this->bindings
            )
        ;

        $this->clear();

        return $this;
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return self
     */
    public function create($data, bool $validate = true): self
    {
        if (is_array($data) || is_object($data)) {
            $this->data($data, $validate);
        }

        $lastInsertId = $this->db
            ->driver($this->driver)
            ->create($this->table, $this->data)
        ;

        if (!empty($lastInsertId)) {
            return $this->fetchById(
                $lastInsertId, \PDO::FETCH_OBJ
            );
        }

        $this->clear(['data']);

        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string|null
     */
    public function getDriver(): ?string
    {
        return $this->driver ?? null;
    }

    /**
     * @param int|null $id Primary key value
     *
     * @throws \Exception
     *
     * @return self
     */
    public function delete($id = null): self
    {
        if (!empty($id) && !is_array($id) && $this->getPrimaryKey()) {
            $this->data([$this->getPrimaryKey() => $id]);
        }

        $this->checkWherePk();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[delete] `%s::where()` is empty.', get_called_class()),
                E_USER_ERROR
            );
        }

        $this->data = $this->db
            ->driver($this->driver)
            ->delete(
                $this->table,
                "WHERE {$this->normalizeProperty($this->where)}",
                $this->bindings
            )
        ;

        $this->clear();

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function getStatement(): Statement
    {
        return $this->buildSqlStatement();
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    protected function buildSqlStatement(): Statement
    {
        if (empty($this->table)) {
            throw new \InvalidArgumentException(
                sprintf('[buildSqlStatement] `%s::table` is empty.', get_called_class()),
                E_USER_ERROR
            );
        }

        if (method_exists($this, '_conditions')) {
            $this->_conditions();
        }

        // Build select
        $this->select = implode(', ', ($this->select ?: ["{$this->table}.*"]));
        $sql = "SELECT {$this->select} FROM {$this->table} ";

        // Build join
        if (!empty($this->join) && is_array($this->join)) {
            $this->join = implode(' ', $this->join);
            $sql .= "{$this->join} ";
        }

        // Build where
        if (!empty($this->where) && is_array($this->where)) {
            $this->where = $this->normalizeProperty(implode(' ', $this->where));
            $sql .= "WHERE{$this->where} ";
        }

        // Build group by
        if (!empty($this->group) && is_array($this->group)) {
            $this->group = implode(', ', $this->group);
            $sql .= "GROUP BY {$this->group} ";
        }

        // Build having
        if (!empty($this->having) && is_array($this->having)) {
            $this->having = $this->normalizeProperty(implode(' ', $this->having));
            $sql .= "HAVING{$this->having} ";
        }

        // Build order by
        if (!empty($this->order) && is_array($this->order)) {
            $this->order = implode(', ', $this->order);
            $sql .= "ORDER BY {$this->order} ";
        }

        // Build limit && offset
        if (!empty($this->limit) && is_int($this->limit)) {
            $this->offset = $this->offset ?: '0';

            if (in_array(config('database.default'), ['dblib', 'sqlsrv'])) {
                $sql .= "OFFSET {$this->offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
            } else {
                $sql .= "LIMIT {$this->limit} OFFSET {$this->offset}";
            }
        }

        // Execute sql
        $this->statement = $this->db
            ->driver($this->driver)
            ->query(trim($sql), $this->bindings)
        ;

        $this->clear();

        return $this->statement;
    }

    /**
     * @param string|array $string
     *
     * @return string
     */
    protected function normalizeProperty($string): string
    {
        if (is_array($string)) {
            $string = implode(' ', $string);
        }

        return preg_replace(
            '/^(and|or)/i', '', trim($string)
        );
    }

    /**
     * @param string|array|null $conditions
     * @param string            $property
     *
     * @return void
     */
    protected function mountProperty($conditions, $property): void
    {
        if (!is_array($this->{$property})) {
            $this->{$property} = [];
        }

        foreach ((array)$conditions as $condition) {
            if (!empty($condition) && !array_search($condition, $this->{$property})) {
                $this->{$property}[] = trim((string)$condition);
            }
        }
    }

    /**
     * @return void
     */
    protected function checkWherePk(): void
    {
        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :pkey";
            $this->bindings['pkey'] = $this->getPrimaryValue();
        }
    }
}
