<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class PostgreSqlConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PostgreSqlConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        $config['port'] = $config['port'] ?? 5432;

        return "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultSchema(\PDO $connection, array $config): void
    {
        if (!empty($config['schema'])) {
            if (is_string($config['schema'])) {
                $config['schema'] = explode('', $config['schema']);
            }

            $connection->exec(sprintf(
                'SET search_path TO %s', implode(
                    ', ', array_map([$connection, 'quote'], $config['schema'])
                )
            ));
        }
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultEncoding(\PDO $connection, array $config): void
    {
        if (!empty($config['charset'])) {
            $connection->exec("SET client_encoding TO {$connection->quote(strtoupper($config['charset']))}");
        }
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultTimezone(\PDO $connection, array $config): void
    {
        if (!empty($config['timezone'])) {
            $connection->exec("SET timezone TO {$connection->quote($config['timezone'])}");
        }
    }
}
