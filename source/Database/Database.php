<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Database {
    use BadMethodCallException;
    use Closure;
    use Core\App;
    use Core\Helpers\Obj;
    use Exception;
    use InvalidArgumentException;
    use PDO;
    use PDOException;
    use RuntimeException;

    /**
     * Class Database
     *
     * @method Statement getPdo()
     * @method Statement fetch($fetchStyle = null, $cursorOrientation = 0, $cursorOffset = 0)
     * @method Statement fetchColumn($column_number = 0)
     * @method Statement fetchObject($class_name = "stdClass", $ctor_args = array())
     * @method Statement fetchAll($fetchStyle = null, $fetchArgument = null, $ctorArgs = null)
     * @method Statement rowCount()
     * @method Statement errorCode()
     * @method Statement errorInfo()
     * @method Statement setAttribute($attribute, $value)
     * @method Statement getAttribute($attribute)
     * @method Statement columnCount()
     * @method Statement getColumnMeta($column)
     * @method Statement setFetchMode($mode, $params)
     * @method Statement nextRowset()
     * @method Statement closeCursor()
     * @method Statement debugDumpParams()
     *
     * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Database extends PDO
    {
        /**
         * @var \PDO
         */
        private static $instance;

        /**
         * @var Statement
         */
        private $statement;

        /**
         * @var array
         */
        private $bindings = [];

        /**
         * Bloqueia a construção da classe.
         *
         * @param string $driver
         *
         * @throws \Exception
         */
        public function __construct($driver)
        {
            try {
                // Variávies
                $driver = strtolower($driver);
                $connections = config('database.connections');

                // Verifica driver
                if (empty($connections[$driver]) || !in_array($driver, PDO::getAvailableDrivers())) {
                    throw new InvalidArgumentException(
                        "Driver \"{$driver}\" de conexão com o banco da dados inválido.", E_ERROR
                    );
                }

                // Configuração do driver
                $config = array_merge($connections[$driver], ['driver' => $driver]);

                // Realiza a conexão com o banco de dados
                parent::__construct(
                    $this->getDsn($config),
                    $config['username'],
                    $config['password'],
                    config('database.options', [])
                );

                // Configurações padrões
                $this->setDefaultStatement();
                $this->setDefaultAttributesAndCommands($config);
                $this->setDefaultSchema($config);
                $this->setDefaultEncoding($config);
                $this->setDefaultTimezone($config);
            } catch (PDOException $e) {
                throw $e;
            }
        }

        /**
         * @param string $method
         * @param mixed  ...$arguments
         *
         * @return mixed
         */
        public function __call($method, $arguments)
        {
            if ($this->statement && method_exists($this->statement, $method)) {
                return $this->statement->{$method}(...$arguments);
            }

            throw new BadMethodCallException(
                sprintf('Call to undefined method %s::%s()', get_class(), $method), E_USER_ERROR
            );
        }

        /**
         * @param string $driver mysql|pgsql|dblib|sqlsrv
         *
         * @throws \Exception
         *
         * @return $this
         */
        public function driver($driver)
        {
            if (!empty($driver)) {
                return Database::getInstance($driver);
            }

            return $this;
        }

        /**
         * Database constructor.
         *
         * @param string $driver
         *
         * @throws \Exception
         *
         * @return $this
         */
        public static function getInstance($driver = null)
        {
            $driver = ($driver ?: config('database.default'));

            if (empty(self::$instance[$driver])) {
                self::$instance[$driver] = new self($driver);
            }

            return self::$instance[$driver];
        }

        /**
         * @param \Closure $callback
         *
         * @throws \Exception
         *
         * @return \Closure|mixed
         */
        public function transaction(Closure $callback)
        {
            try {
                $this->beginTransaction();
                $callback = $callback($this);
                $this->commit();

                return $callback;
            } catch (Exception $e) {
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
         * @return Statement
         */
        public function create($table, $data)
        {
            // Variávies
            $table = (string)$table;
            $data = $this->toData($data);
            $values = [];

            // Previne os binds caso exista
            $this->bindings = [];

            // Monta os valores conforme se é um array multimensional ou um array simples
            if (!empty($data[0])) {
                foreach ($data as $i => $item) {
                    $data = ($this->emitEvent("{$table}:creating", $item) ?: $item);
                    $values[] = ':' . implode("_{$i}, :", array_keys($data)) . "_{$i}";

                    foreach ($data as $k => $v) {
                        $this->setBindings(["{$k}_{$i}" => $v]);
                    }
                }

                $values = '(' . implode('), (', $values) . ')';
            } else {
                $data = ($this->emitEvent("{$table}:creating", $data) ?: $data);
                $values = '(:' . implode(', :', array_keys($data)) . ')';
                $this->setBindings($data);
            }

            // Executa a query
            $columns = implode(', ', array_keys($data));
            $statement = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
            $statement = $this->query($statement);

            // Evento tbName:created
            $this->emitEvent("{$table}:created", $this->lastInsertId());

            return $statement;
        }

        /**
         * @param mixed  $data
         * @param string $type
         *
         * @return array
         */
        public function toData($data, $type = 'array')
        {
            // Variávies
            $type = (string)($type ?: 'array');

            switch ($type) {
                case 'array':
                    if (is_object($data)) {
                        $data = Obj::toArray($data);
                    }
                    break;

                case 'object':
                    if (is_array($data)) {
                        $data = Obj::fromArray($data);
                    }
                    break;
            }

            return $data;
        }

        /**
         * @param string       $statement
         * @param string|array $bindings
         * @param array        $driverOptions
         *
         * @throws \Exception
         *
         * @return Statement
         */
        public function query($statement, $bindings = null, $driverOptions = [])
        {
            try {
                if (empty($statement)) {
                    throw new InvalidArgumentException(
                        'Parâmetro "$statement" não pode ser vázia.', E_ERROR
                    );
                }

                // Execute
                $this->statement = $this->prepare($statement, $driverOptions);
                $this->setBindings($bindings);
                $this->bindValues();
                $this->statement->execute();

                return $this->statement;
            } catch (PDOException $e) {
                throw $e;
            }
        }

        /**
         * @param string       $table
         * @param array|object $data
         * @param string       $condition
         * @param string|array $bindings
         *
         * @throws \Exception
         *
         * @return mixed
         */
        public function update($table, $data, $condition, $bindings = null)
        {
            // Variávies
            $table = (string)$table;
            $data = $this->toData($data);
            $condition = (string)$condition;
            $set = [];

            // Verifica registro
            $updated = $this->read($table, $condition, $bindings)->fetch();
            if (empty($this->toData($updated))) {
                return false;
            }

            // Evento tbName:updating
            $data = ($this->emitEvent("{$table}:updating", $data) ?: $data);

            // Trata os dados passado para atualzar
            foreach ($data as $key => $value) {
                $bind = $key;
                $value = filter_var($value, FILTER_DEFAULT);

                // Atualiza os dados do updated
                if ($this->isFetchObject()) {
                    $updated->{$key} = $value;
                } else {
                    $updated[$key] = $value;
                }

                // Verifica se já existe algum bind igual
                if (!empty($this->bindings[$bind])) {
                    $uniqid = mt_rand(1, time());
                    $bind = "{$bind}_{$uniqid}";
                }

                $set[] = "{$key} = :{$bind}";
                $this->bindings[$bind] = $value;
            }

            $set = implode(', ', $set);

            // Executa a query
            $statement = "UPDATE {$table} SET {$set} {$condition}";
            $this->query($statement, $bindings);

            // Evento tbName:updated
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
         * @return Statement
         */
        public function read($table, $condition = null, $bindings = null)
        {
            // Executa a query
            $statement = "SELECT {$table}.* FROM {$table} {$condition}";

            return $this->query($statement, $bindings);
        }

        /**
         * @param int $style
         *
         * @return bool
         */
        public function isFetchObject($style = null)
        {
            $allowed = [PDO::FETCH_OBJ, PDO::FETCH_CLASS];
            $fetchMode = $style ?: $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);

            if (in_array($fetchMode, $allowed)) {
                return true;
            }

            return false;
        }

        /**
         * @param string       $table
         * @param string       $condition
         * @param string|array $bindings
         *
         * @throws \Exception
         *
         * @return mixed
         */
        public function delete($table, $condition, $bindings = null)
        {
            // Variávies
            $table = (string)$table;
            $condition = (string)$condition;

            // Verifica registro
            $deleted = $this->read($table, $condition, $bindings)->fetch();
            if (empty($this->toData($deleted))) {
                return false;
            }

            // Evento tbName:deleting
            $this->emitEvent("{$table}:deleting", $deleted);

            // Executa a query
            $statement = "DELETE FROM {$table} {$condition}";
            $this->query($statement, $bindings);

            // Evento tbName:deleted
            $this->emitEvent("{$table}:deleted", $deleted);

            return $deleted;
        }

        /**
         * @param array $config
         *
         * @return string
         */
        protected function getDsn(array $config)
        {
            switch ($config['driver']) {
                case 'pgsql':
                    return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
                    break;

                case 'sqlsrv':
                case 'dblib':
                    return empty($config['dsn'])
                        ? "dblib:version=7.0;charset=UTF-8;host={$config['host']};dbname={$config['database']}"
                        : $config['dsn'];
                    break;

                case 'mysql':
                    return !empty($config['unix_socket'])
                        ? "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}"
                        : "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
                    break;

                default:
                    throw new InvalidArgumentException(
                        "Driver `{$config['driver']}` inválido para criação do dsn.", E_USER_ERROR
                    );
            }
        }

        /**
         * {@inheritdoc}
         */
        protected function setDefaultStatement()
        {
            $this->setAttribute(
                PDO::ATTR_STATEMENT_CLASS, [Statement::class, [$this]]
            );
        }

        /**
         * @param array $config
         */
        protected function setDefaultAttributesAndCommands(array $config)
        {
            if (!empty($config['attributes'])) {
                foreach ((array)$config['attributes'] as $key => $value) {
                    $this->setAttribute($key, $value);
                }
            }

            // Comandos sql na inicialização
            if (!empty($config['commands'])) {
                foreach ((array)$config['commands'] as $command) {
                    $this->exec($command);
                }
            }
        }

        /**
         * @param array $config
         */
        protected function setDefaultSchema(array $config)
        {
            if ('pgsql' == $config['driver'] && !empty($config['schema'])) {
                if (is_string($config['schema'])) {
                    $config['schema'] = explode('', $config['schema']);
                }

                $this->exec(sprintf(
                    'SET search_path TO %s', implode(
                        ', ', array_map([$this, 'quote'], $config['schema'])
                    )
                ));
            } elseif ('sqlite' !== $config['driver'] && !empty($config['database'])) {
                $this->exec("USE {$config['database']}");
            }
        }

        /**
         * @param array $config
         */
        protected function setDefaultEncoding(array $config)
        {
            if (!empty($config['charset'])) {
                if ('pgsql' == $config['driver']) {
                    $this->exec("SET client_encoding TO {$this->quote(strtoupper($config['charset']))}");
                } elseif ('mysql' == $config['driver']) {
                    $encoding = "SET NAMES {$this->quote($config['charset'])}";

                    if (!empty($config['collation'])) {
                        $encoding .= " COLLATE {$this->quote($config['collation'])}";
                    }

                    $this->exec($encoding);
                } elseif ('sqlsrv' == $config['driver']) {
                    if ('utf8' == $config['charset'] || PDO::SQLSRV_ENCODING_UTF8 == $config['charset']) {
                        $this->setAttribute(
                            PDO::SQLSRV_ATTR_ENCODING, PDO::SQLSRV_ENCODING_UTF8
                        );
                    }
                }
            }
        }

        /**
         * @param array $config
         */
        protected function setDefaultTimezone(array $config)
        {
            if (!empty($config['timezone'])) {
                if ('pgsql' == $config['driver']) {
                    $this->exec("SET timezone TO {$this->quote($config['timezone'])}");
                } elseif ('mysql' == $config['driver']) {
                    $this->exec("SET time_zone = {$this->quote($config['timezone'])}");
                }
            }
        }

        /**
         * @param string $name
         * @param mixed ... (Opcional) Argumento(s)
         *
         * @return mixed
         */
        protected function emitEvent($name = null)
        {
            $event = App::getInstance()
                ->resolve('event')
            ;

            if (!empty($name) && $event) {
                // Retorna o evento emitido
                $arguments = func_get_args();
                array_shift($arguments);

                return $event->emit(
                    (string)$name, ...$arguments
                );
            }

            return false;
        }

        /**
         * @param string|array $bindings
         */
        protected function setBindings($bindings)
        {
            if (!empty($bindings)) {
                // Se for string da o parse e transforma em array
                if (is_string($bindings)) {
                    if (function_exists('mb_parse_str')) {
                        mb_parse_str($bindings, $bindings);
                    } else {
                        parse_str($bindings, $bindings);
                    }
                }

                // Filtra os valores dos bindings
                foreach ($bindings as $key => $value) {
                    $this->bindings[$key] = filter_var(
                        $value, FILTER_DEFAULT
                    );
                }
            }
        }

        /**
         * Executa os bindings e trata os valores.
         */
        protected function bindValues()
        {
            if (!$this->statement instanceof Statement) {
                throw new RuntimeException(
                    'Propriedade "->statement" não é uma instância de "\PDOStatement".', E_USER_ERROR
                );
            }

            if (!empty($this->bindings) && is_array($this->bindings)) {
                foreach ($this->bindings as $key => $value) {
                    if (is_string($key) && in_array($key, ['limit', 'offset', 'l', 'o'])) {
                        $value = (int)$value;
                    }

                    $value = ((empty($value) && '0' != $value)
                        ? null
                        : filter_var($value, FILTER_DEFAULT));

                    $this->statement->bindValue(
                        (is_string($key) ? ":{$key}" : ((int)$key + 1)),
                        $value,
                        (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR)
                    );
                }
            }

            // Reseta os binds
            $this->bindings = [];
        }
    }
}
