<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 02/11/2019 Vagner Cardoso
 */

namespace Core\Database\Connection;

/**
 * Class SQLiteConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SQLiteConnection extends Connection
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config): string
    {
        if ('memory' == $config['database']) {
            return 'sqlite::memory:';
        }

        return "sqlite:{$config['database']}";
    }

    /**
     * @param array $config
     */
    protected function validateConfig(array &$config): void
    {
        if (empty($config['database'])) {
            throw new \InvalidArgumentException(
                "'sqlite' database not configured.", E_USER_ERROR
            );
        }

        if ('memory' !== $config['database'] && !realpath($config['database'])) {
            throw new \InvalidArgumentException(
                "'sqlite' database not exists in path {$config['database']}", E_USER_ERROR
            );
        }
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultSchema(\PDO $connection, array $config): void
    {
        // TODO
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultEncoding(\PDO $connection, array $config): void
    {
        // TODO
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    protected function setDefaultTimezone(\PDO $connection, array $config): void
    {
        // TODO
    }
}
