<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace Core\Database {
    /**
     * Class Statement.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class Statement extends \PDOStatement
    {
        /**
         * @var Database
         */
        protected $db;

        /**
         * Statement constructor.
         *
         * @param Database $db
         */
        protected function __construct(Database $db)
        {
            $this->db = $db;
        }

        /**
         * @return Database
         */
        public function getPdo()
        {
            return $this->db;
        }

        /**
         * @param string $name
         *
         * @return string
         */
        public function lastInsertId($name = null)
        {
            return $this->db->lastInsertId($name);
        }

        /**
         * @return int
         */
        public function rowCount()
        {
            $rowCount = parent::rowCount();

            if (-1 === $rowCount) {
                $rowCount = count($this->fetchAll());
            }

            return $rowCount;
        }

        /**
         * @param int   $fetchStyle
         * @param mixed $fetchArgument
         * @param array $ctorArgs
         *
         * @return array|object
         */
        public function fetchAll($fetchStyle = null, $fetchArgument = null, $ctorArgs = null)
        {
            if (empty($fetchStyle)) {
                $fetchStyle = $this->db->getAttribute(
                    \PDO::ATTR_DEFAULT_FETCH_MODE
                );
            }

            if (\PDO::FETCH_CLASS === $fetchStyle && !class_exists($fetchArgument)) {
                $fetchArgument = 'stdClass';
            }

            if (\PDO::FETCH_BOTH === $fetchStyle) {
                return parent::fetchAll();
            }
            if (\PDO::FETCH_CLASS === $fetchStyle) {
                return parent::fetchAll($fetchStyle, $fetchArgument, $ctorArgs);
            }
            if (in_array($fetchStyle, [\PDO::FETCH_ASSOC, \PDO::FETCH_NUM, \PDO::FETCH_OBJ])) {
                return parent::fetchAll($fetchStyle);
            }

            return parent::fetchAll($fetchStyle, $fetchArgument);
        }

        /**
         * @param mixed $fetchStyle
         * @param int   $cursorOrientation
         * @param int   $cursorOffset
         *
         * @return array|object
         */
        public function fetch($fetchStyle = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
        {
            if ($this->db->isFetchObject($fetchStyle) || class_exists($fetchStyle)) {
                // Verifica o fetchStyle
                if (empty($fetchStyle) || !class_exists($fetchStyle)) {
                    $fetchStyle = 'stdClass';
                }

                // Object style
                return parent::fetchObject($fetchStyle);
            }

            // Default style
            return parent::fetch(
                $fetchStyle, $cursorOrientation, $cursorOffset
            );
        }
    }
}
