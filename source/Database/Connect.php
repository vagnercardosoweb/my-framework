<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 14/12/2019 Vagner Cardoso
 */

namespace Core\Database;

use Core\Database\Connection\MySqlConnection;
use Core\Database\Connection\PostgreSqlConnection;
use Core\Database\Connection\SQLiteConnection;
use Core\Database\Connection\SqlServerConnection;

/**
 * Class Connect.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Connect
{
    /**
     * @var array[\PDO]
     */
    private static $instances;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var string
     */
    private $defaultConnection = 'mysql';

    /**
     * @param array  $config
     * @param string $driver
     *
     * @return $this
     */
    public function addConnection(array $config, string $driver): Connect
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
    public function connection(?string $driver = null): Database
    {
        $driver = $driver ?? $this->getDefaultConnection();

        if (empty($this->connections[$driver])) {
            throw new \Exception("Connection {$driver} does not exist configured.", E_ERROR);
        }

        $config = $this->connections[$driver];
        $config['driver'] = $config['driver'] ?? $driver;

        if (empty(self::$instances[$driver])) {
            switch ($config['driver']) {
                case 'mysql':
                    self::$instances[$driver] = (new MySqlConnection())->connect($config);
                    break;

                case 'pgsql':
                    self::$instances[$driver] = (new PostgreSqlConnection())->connect($config);
                    break;

                case 'sqlsrv':
                    self::$instances[$driver] = (new SqlServerConnection())->connect($config);
                    break;

                case 'sqlite':
                    self::$instances[$driver] = (new SQLiteConnection())->connect($config);
                    break;
            }
        }

        $this->pdo = self::$instances[$driver];

        return new Database($this);
    }

    /**
     * @return string
     */
    public function getDefaultConnection(): string
    {
        return $this->defaultConnection;
    }

    /**
     * @param string $defaultConnection
     *
     * @return Connect
     */
    public function setDefaultConnection(string $defaultConnection): Connect
    {
        $this->defaultConnection = $defaultConnection;

        return $this;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }
}
