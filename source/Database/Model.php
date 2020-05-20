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

use Core\Config;
use Core\Database\Connection\Statement;
use Core\Helpers\Helper;
use Core\Helpers\Obj;

/**
 * Class Model.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model implements \ArrayAccess, \JsonSerializable
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
     * @var string
     */
    protected $foreignKey;

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
     * @return void
     */
    public function __clone()
    {
        $data = clone $this->toObject();
        unset($data->{$this->primaryKey});

        $this->data = $data;
        $this->reset = [];
        $this->statement = null;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($provider = app()->resolve($name)) {
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
     */
    public function __set(string $name, $value): void
    {
        $this->data = Obj::fromArray($this->data);
        $this->data->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data->{$name});
    }

    /**
     * @param string $name
     */
    public function __unset(string $name): void
    {
        unset($this->data->{$name});
    }

    /**
     * @return object
     */
    public function toObject(): object
    {
        return Obj::fromArray($this->data);
    }

    /**
     * @param string $name
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
     */
    public function offsetUnset($name): void
    {
        $this->__unset($name);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Obj::toArray($this->data);
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
     * @param bool $replaceBindings
     *
     * @return string
     */
    public function getQuery(bool $replaceBindings = false): string
    {
        if (empty($this->table)) {
            throw new \InvalidArgumentException(
                sprintf('[getQuery] `%s::table` is empty.', get_called_class())
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

            if (in_array(Config::get('database.default'), ['dblib', 'sqlsrv'])) {
                $sql .= "OFFSET {$this->offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
            } else {
                $sql .= "LIMIT {$this->limit} OFFSET {$this->offset}";
            }
        }

        if (true === $replaceBindings) {
            $sql = $this->replaceQueryBindings($sql);
        }

        return trim($sql);
    }

    /**
     * @param array $properties
     * @param bool  $reset
     *
     * @return $this
     */
    public function clear(array $properties = [], bool $reset = false): self
    {
        $notReset = array_diff([
            'table',
            'primaryKey',
            'foreignKey',
            'driver',
            'fetchStyle',
            'statement',
            'data',
        ], $properties);

        $reflection = new \ReflectionClass(get_class($this));

        foreach ($reflection->getProperties() as $property) {
            if (!in_array($property->getName(), $notReset)) {
                if (empty($properties) || in_array($property->getName(), $properties)) {
                    if ($reset) {
                        $this->reset[$property->getName()] = true;
                    } else {
                        $value = null;
                        preg_match('/@var\s+(array|object|string)/im', $property->getDocComment(), $matches);

                        if (!empty($matches[1])) {
                            $value = 'array' === $matches[1] ? [] : new \stdClass();
                        }

                        $this->{$property->getName()} = $value;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array $properties
     *
     * @return $this
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
    public function count(string $column = '1'): int
    {
        $fetch = $this->select("COUNT({$column}) AS count")
            ->order('count DESC')->limit(1)
            ->buildSqlStatement()
            ->fetch(\PDO::FETCH_OBJ)
        ;

        return $fetch ? (int)$fetch->count : 0;
    }

    /**
     * @param int $limit
     * @param int $offset
     *
     * @return $this
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
     * @return $this
     */
    public function offset($offset): self
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * @param array|string $order
     *
     * @return $this
     */
    public function order($order): self
    {
        $this->mountProperty($order, 'order');

        return $this;
    }

    /**
     * @param array|string $select
     *
     * @return $this
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
     * @return $this|string
     */
    public function table(?string $table = null)
    {
        if (!empty($table)) {
            $this->table = (string)$table;

            return $this;
        }

        return $this->table;
    }

    /**
     * @param array|string $join
     *
     * @return $this
     */
    public function join($join): self
    {
        $this->mountProperty($join, 'join');

        return $this;
    }

    /**
     * @param array|string $group
     *
     * @return $this
     */
    public function group($group): self
    {
        $this->mountProperty($group, 'group');

        return $this;
    }

    /**
     * @param array|string $having
     *
     * @return $this
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
        $db = $this->db->driver($this->driver);

        return $db->transaction($callback, $this);
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function save($data = [], bool $validate = true): self
    {
        if ($this->fetchById($this->getPrimaryValue())) {
            $this->update($data, $validate);

            return $this;
        }

        return $this->create($data, $validate);
    }

    /**
     * @param int|array $id
     * @param int       $fetchStyle
     *
     * @throws \Exception
     *
     * @return $this|$this[]|null
     */
    public function fetchById($id, ?int $fetchStyle = null)
    {
        if ($this->primaryKey && $id) {
            if (is_array($id)) {
                array_unshift(
                    $this->where,
                    sprintf("AND {$this->table}.{$this->primaryKey} IN (%s)", implode(',', $id))
                );

                return $this->fetchAll($fetchStyle);
            }

            array_unshift($this->where, "AND {$this->table}.{$this->primaryKey} = :u{$this->primaryKey}");
            $this->bindings["u{$this->primaryKey}"] = filter_params($id)[0];
        }

        if (empty($this->where)) {
            return null;
        }

        return $this->fetch($fetchStyle);
    }

    /**
     * @param int   $fetchStyle
     * @param mixed $fetchArgument
     *
     * @throws \Exception
     *
     * @return $this[]
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

        foreach ($rows as $index => $row) {
            if (method_exists($this, '_row')) {
                $this->_row($row);
            }

            $rows[$index] = $row;
        }

        $this->statement->closeCursor();

        return $rows;
    }

    /**
     * @param int $fetchStyle
     *
     * @throws \Exception
     *
     * @return $this|null
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

        $row = $this->statement->fetch($fetchStyle) ?: null;

        if ($row) {
            if (method_exists($this, '_row')) {
                $this->_row($row);
            }
        }

        $this->statement->closeCursor();

        return $row;
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
     * @return $this[]|null
     */
    public function update($data = [], bool $validate = true): ?array
    {
        $this->data($data, $validate);
        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[update] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = $this->db
            ->driver($this->driver)
            ->update(
                $this->table,
                $this->data,
                "WHERE {$this->normalizeProperty($this->where)}",
                $this->bindings
            )
        ;

        $this->clear();

        if (!$rows) {
            return null;
        }

        foreach ($rows as $key => $row) {
            $new = new static();
            $new->data = $row;
            $rows[$key] = $new;
        }

        return $rows;
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @return $this
     */
    public function data($data = [], bool $validate = true): self
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
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function create($data = [], bool $validate = true): self
    {
        $this->data($data, $validate);

        $lastInsertId = $this->db
            ->driver($this->driver)
            ->create($this->table, $this->data)
        ;

        $new = clone $this;

        if ($lastInsertId && $this->primaryKey) {
            $new->{$this->primaryKey} = $lastInsertId;
        }

        $this->clear(['data']);

        return $new;
    }

    /**
     * @return string|null
     */
    public function getForeignKey(): ?string
    {
        return $this->foreignKey;
    }

    /**
     * @param mixed  $value
     * @param string $column
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function whereBy(string $column, $value): self
    {
        $this->where("AND {$this->table}.{$column} = :{$column}");
        $this->bindings([$column => $value]);

        return $this;
    }

    /**
     * @param string|array $where
     * @param string|array $bindings
     *
     * @return $this
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
     * @return $this
     */
    public function bindings($bindings): self
    {
        Helper::parseStr($bindings, $this->bindings);

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
     * @param int|null $id
     *
     * @throws \Exception
     *
     * @return $this[]|null
     */
    public function delete(?int $id = null): ?array
    {
        if (!empty($id) && !is_array($id) && $this->primaryKey) {
            $this->data([$this->primaryKey => $id]);
        }

        $this->mountWherePrimaryKey();

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[delete] `%s::where()` is empty.', get_called_class())
            );
        }

        $rows = $this->db
            ->driver($this->driver)
            ->delete(
                $this->table,
                "WHERE {$this->normalizeProperty($this->where)}",
                $this->bindings
            )
        ;

        if (!$rows) {
            return null;
        }

        foreach ($rows as $key => $row) {
            $new = new static();
            $new->data = $row;
            $rows[$key] = $new;
        }

        return $rows;
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    public function getStatement(): Statement
    {
        $statement = $this->db->driver($this->driver)->prepare($this->getQuery());
        $statement->bindValues($this->bindings);

        return $statement;
    }

    /**
     * @param string $driver
     *
     * @return $this
     */
    public function driver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return \Core\Database\Connection\Statement
     */
    protected function buildSqlStatement(): Statement
    {
        $this->statement = $this->db
            ->driver($this->driver)
            ->query(
                $this->getQuery(),
                $this->bindings
            )
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
     * @param string $sql
     *
     * @return string
     */
    protected function replaceQueryBindings($sql)
    {
        $keys = array_keys($this->bindings);
        $keys = explode(',', ':'.implode(',:', $keys));
        $values = array_map(function ($bind) {
            if (!is_numeric($bind)) {
                $bind = $this->db->quote($bind);
            }

            return $bind;
        }, array_values($this->bindings));

        return str_replace($keys, $values, $sql);
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
    protected function mountWherePrimaryKey(): void
    {
        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :pkey";
            $this->bindings['pkey'] = $this->getPrimaryValue();
        }
    }
}
