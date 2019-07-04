<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class MySqlConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MySqlConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config): \PDO
    {
        try {
            // Connection
            $dsn = $this->getDsn($config);
            $config['driver'] = $config['driver'] ?? 'mysql';
            $config['options'] = $this->getOptions($config);

            return $this->createConnection($dsn, $config);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        if (!empty($config['unix_socket'])) {
            return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
        }

        $config['port'] = $config['port'] ?? 3306;

        return "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultSchema(\PDO $connection, array $config): void
    {
        if (!empty($config['database'])) {
            $connection->exec("USE {$config['database']};");
        }
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultEncoding(\PDO $connection, array $config): void
    {
        if (!empty($config['charset'])) {
            $encoding = "SET NAMES {$connection->quote($config['charset'])}";
            $encoding .= (!empty($config['collation']) ? " COLLATE {$connection->quote($config['collation'])}" : '');
            $connection->exec("{$encoding};");
        }
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultTimezone(\PDO $connection, array $config): void
    {
        if (!empty($config['timezone'])) {
            $connection->exec("SET time_zone = {$connection->quote($config['timezone'])};");
        }
    }
}
