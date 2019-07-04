<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
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
     * @var \PDO
     */
    private $pdo;

    /**
     * @var array[\PDO]
     */
    private static $instances;

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @var string
     */
    private $defaultConnection;

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
        $driver = $driver ?? $this->getDefaultConnection() ?? 'mysql';
        $config = $this->connections[$driver] ?? null;

        if (empty($config)) {
            throw new \RuntimeException(
            "Driver '{$driver}' not configured in database.", E_USER_ERROR
        );
        }

        if (empty(self::$instances[$driver])) {
            switch ($driver) {
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
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
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
}
