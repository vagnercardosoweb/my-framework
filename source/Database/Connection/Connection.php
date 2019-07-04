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
 * Class Connection.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Connection
{
    /**
     * Default options.
     *
     * @var array
     */
    protected $options = [
        \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_PERSISTENT => false,
        \PDO::ATTR_STRINGIFY_FETCHES => false,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    /**
     * @return array
     */
    public function getSupportedDrivers(): array
    {
        return ['mysql', 'pgsql', 'sqlite', 'sqlsrv'];
    }

    /**
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        return array_intersect(
            $this->getSupportedDrivers(),
            str_replace(['dblib', 'pdo_dblib'], 'sqlsrv', \PDO::getAvailableDrivers())
        );
    }

    /**
     * @return array
     */
    public function getDefaultOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string $dsn
     * @param array  $config
     *
     * @return \PDO
     */
    protected function createConnection(string $dsn, array $config): \PDO
    {
        try {
            $this->validateConfig($config);

            list($username, $password) = [
                $config['username'] ?? null,
                $config['password'] ?? null,
            ];

            $options = $config['options'] ?? [];
            $connection = new \PDO($dsn, $username, $password, $options);

            $this->setDefaultStatement($connection);
            $this->setDefaultAttributesAndCommands($connection, $config);
            $this->setDefaultSchema($connection, $config);
            $this->setDefaultEncoding($connection, $config);
            $this->setDefaultTimezone($connection, $config);

            return $connection;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param array $config
     */
    protected function validateConfig(array &$config): void
    {
        if (empty($config['host'])) {
            throw new \InvalidArgumentException(
                "'{$config['driver']}' host not configured.", E_USER_ERROR
            );
        }

        if (empty($config['username'])) {
            throw new \InvalidArgumentException(
                "'{$config['driver']}' username not configured.", E_USER_ERROR
            );
        }

        if (empty($config['password']) && empty($config['notPassword'])) {
            throw new \InvalidArgumentException(
                "'{$config['driver']}' password not configured.", E_USER_ERROR
            );
        }

        if (empty($config['database']) && empty($config['notDatabase'])) {
            throw new \InvalidArgumentException(
                "'{$config['driver']}' database not configured.", E_USER_ERROR
            );
        }
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function getOptions(array $config): array
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }

    /**
     * @param \PDO $connection
     */
    protected function setDefaultStatement(\PDO $connection): void
    {
        $connection->setAttribute(
            \PDO::ATTR_STATEMENT_CLASS, [Statement::class, [$connection]]
        );
    }

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    abstract protected function setDefaultSchema(\PDO $connection, array $config): void;

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    abstract protected function setDefaultEncoding(\PDO $connection, array $config): void;

    /**
     * @param \PDO  $connection
     * @param array $config
     */
    abstract protected function setDefaultTimezone(\PDO $connection, array $config): void;

    /**
     * @param array $config
     * @param \PDO  $connection
     */
    protected function setDefaultAttributesAndCommands(\PDO $connection, array $config): void
    {
        if (!empty($config['attributes'])) {
            foreach ((array)$config['attributes'] as $key => $value) {
                $connection->setAttribute($key, $value);
            }
        }

        if (!empty($config['commands'])) {
            foreach ((array)$config['commands'] as $command) {
                $connection->exec($command);
            }
        }
    }
}
