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
use Core\Helpers\Helper;
use Core\Helpers\Obj;

/**
 * Class Model.
 *
 * @property \Slim\Collection        $settings
 * @property \Slim\Http\Environment  $environment
 * @property \Slim\Http\Request      $request
 * @property \Slim\Http\Response     $response
 * @property \Slim\Router            $router
 * @property \Core\View              $view
 * @property \Core\Session\Session   $session
 * @property \Core\Session\Flash     $flash
 * @property \Core\Mailer\Mailer     $mailer
 * @property \Core\Password\Password $password
 * @property \Core\Encryption        $encryption
 * @property \Core\Jwt               $jwt
 * @property \Core\Logger            $logger
 * @property \Core\Event             $event
 * @property \Core\Database\Database $db
 * @property \Core\Database\Connect  $connect
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var \Core\Database\Connection\Statement
     */
    protected $statement;

    /**
     * @var string
     */
    protected $driver;

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
     */
    public function __set($name, $value)
    {
        $this->data = Obj::fromArray($this->data);
        $this->data->{$name} = $value;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->{$name});
    }

    /**
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->data->{$name});
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return Obj::toArray($this->data);
    }

    /**
     * @return object
     */
    public function toObject()
    {
        return Obj::fromArray($this->data);
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->buildSqlStatement()
            ->rowCount()
        ;
    }

    /**
     * @param string $column
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count($column = '1')
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
     * @return $this
     */
    public function limit($limit, $offset = 0)
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
    public function offset($offset)
    {
        $this->offset = (int)$offset;

        return $this;
    }

    /**
     * @param string|array|null $order
     *
     * @return $this
     */
    public function order($order)
    {
        $this->mountProperty($order, 'order');

        return $this;
    }

    /**
     * @param mixed $select
     *
     * @return $this
     */
    public function select($select = '*')
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
     * @return $this
     */
    public function join($join)
    {
        $this->mountProperty($join, 'join');

        return $this;
    }

    /**
     * @param string|array|null $group
     *
     * @return $this
     */
    public function group($group)
    {
        $this->mountProperty($group, 'group');

        return $this;
    }

    /**
     * @param string|array|null $having
     *
     * @return $this
     */
    public function having($having)
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
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function save($data = [], bool $validate = true): self
    {
        $this->reset();
        $this->data($data, $validate);

        if ($row = $this->fetchById($this->getPrimaryValue())) {
            $where = implode(' ', $this->where);

            if (!empty($this->getPrimaryValue())) {
                $where = "{$this->table}.{$this->getPrimaryKey()} = :pkid {$where}";
                $this->bindings['pkid'] = $this->getPrimaryValue();
            }

            $row->data = $this->db
                ->driver($this->driver)
                ->update(
                    $this->table, $this->data,
                    sprintf('WHERE %s', $this->normalizeProperty($where)),
                    $this->bindings
                )
            ;

            return $row;
        }

        $lastInsertId = $this->db
            ->driver($this->driver)
            ->create($this->table, $this->data)
        ;

        if (!empty($lastInsertId)) {
            return $this->fetchById($lastInsertId);
        }

        // Clear conditions query
        $this->clear();

        return $this;
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @return $this
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
     * @return string
     */
    public function getPrimaryValue()
    {
        return $this->{$this->getPrimaryKey()};
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string|array|null $properties
     *
     * @return $this
     */
    public function reset($properties = [])
    {
        if (empty($properties)) {
            try {
                $reflection = new \ReflectionClass(get_class($this));

                foreach ($reflection->getProperties() as $property) {
                    $notReset = (!empty($this->notReset) ? $this->notReset : []);

                    if (!in_array($property->getName(), $notReset)) {
                        $this->reset[$property->getName()] = true;
                    }
                }
            } catch (\ReflectionException $e) {
            }
        } else {
            if (is_string($properties)) {
                $properties = explode(',', $properties);
            }

            foreach ($properties as $property) {
                $this->reset[trim($property)] = true;
            }
        }

        return $this;
    }

    /**
     * @param int|array $id
     *
     * @throws \Exception
     *
     * @return $this|array[$this]|null
     */
    public function fetchById($id): ?self
    {
        if (!empty($id)) {
            if (is_array($id)) {
                $this->where(sprintf(
                    "AND {$this->table}.{$this->primaryKey} IN (%s)",
                    implode(',', $id)
                ));

                return $this->fetchAll();
            }

            $this->where("AND {$this->table}.{$this->primaryKey} = :pkbyid", [
                'pkbyid' => filter_var($id, FILTER_DEFAULT),
            ]);
        }

        if (empty($this->where)) {
            return null;
        }

        return $this->fetch();
    }

    /**
     * @param string|array $where
     * @param string|array $bindings
     *
     * @return $this
     */
    public function where($where, $bindings = null)
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
    public function bindings($bindings)
    {
        Helper::parseStr($bindings, $this->bindings);

        return $this;
    }

    /**
     * @throws \Exception
     *
     * @return array[$this]|null
     */
    public function fetchAll()
    {
        $this->buildSqlStatement();

        $rows = $this->statement->fetchAll(
            \PDO::FETCH_CLASS,
            get_called_class()
        );

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
     * @throws \Exception
     *
     * @return $this|null
     */
    public function fetch(): ?self
    {
        $this->buildSqlStatement();

        $row = $this->statement->fetch(
            get_called_class()
        );

        if (!empty($row)) {
            if (method_exists($this, '_row')) {
                $this->_row($row);
            }
        }

        return $row ?: null;
    }

    /**
     * @param int|null $id Primary key value
     *
     * @throws \Exception
     *
     * @return $this|null
     */
    public function delete($id = null): ?self
    {
        if (!empty($id) && $this->getPrimaryKey()) {
            $this->data([$this->getPrimaryKey() => $id]);
        }

        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :pkid ";
            $this->bindings['pkid'] = $this->getPrimaryValue();
        }

        if (is_array($this->where)) {
            $this->where = implode(' ', $this->where);
        }

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
    protected function buildSqlStatement()
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
            $sql .= "WHERE {$this->where} ";
        }

        // Build group by
        if (!empty($this->group) && is_array($this->group)) {
            $this->group = implode(', ', $this->group);
            $sql .= "GROUP BY {$this->group} ";
        }

        // Build having
        if (!empty($this->having) && is_array($this->having)) {
            $this->having = $this->normalizeProperty(implode(' ', $this->having));
            $sql .= "HAVING {$this->having} ";
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
            ->query($sql, $this->bindings)
        ;

        $this->clear();

        return $this->statement;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function normalizeProperty(string $string)
    {
        $chars = ['and', 'AND', 'or', 'OR'];

        foreach ($chars as $char) {
            $len = mb_strlen($char);

            if (mb_substr($string, 0, $len) === (string)$char) {
                $string = trim(mb_substr($string, $len));
            }
        }

        return $string;
    }

    /**
     * @return $this
     */
    protected function clear(): self
    {
        try {
            $reflection = new \ReflectionClass(get_class($this));

            foreach ($reflection->getProperties() as $property) {
                if (!in_array($property->getName(), [
                    'statement',
                    'driver',
                    'table',
                    'primaryKey',
                    'data',
                ])) {
                    $value = null;

                    if (preg_match('/@var\s+(array)/im', $property->getDocComment())) {
                        $value = [];
                    }

                    $this->{$property->getName()} = $value;
                }
            }
        } catch (\ReflectionException $e) {
        }

        return $this;
    }

    /**
     * @param string|array|null $conditions
     * @param string            $property
     */
    protected function mountProperty($conditions, $property)
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
}
