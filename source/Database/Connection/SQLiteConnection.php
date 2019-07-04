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
 * Class SQLiteConnection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SQLiteConnection extends Connection
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
            $config['driver'] = $config['driver'] ?? 'sqlite';
            $config['options'] = $this->getOptions($config);

            return $this->createConnection($dsn, $config);
        } catch (\PDOException $e) {
            throw $e;
        }
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
