<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Phinx;

use Core\App;
use Phinx\Migration\AbstractMigration;

/**
 * Class Migration.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Migration extends AbstractMigration
{
    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $engine = 'InnoDB';

    /**
     * @var string
     */
    protected $collation = 'utf8_general_ci';

    /**
     * @var string|bool
     */
    protected $primaryKey = false;

    /**
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return App::getInstance()
            ->resolve($name)
        ;
    }

    /**
     * @param string|null $table
     * @param array       $options
     *
     * @throws \Exception
     *
     * @return \Phinx\Db\Table
     */
    public function table($table = null, $options = [])
    {
        $table = $table ?? $this->table;

        if (empty($table)) {
            throw new \Exception(
                sprintf('Table not defined in %s.', get_class($this)),
                E_ERROR
            );
        }

        return parent::table($table, array_merge([
            'id' => $this->primaryKey,
            'engine' => $this->engine,
            'collation' => $this->collation,
            'primary_key' => $this->primaryKeys,
        ], $options));
    }
}
