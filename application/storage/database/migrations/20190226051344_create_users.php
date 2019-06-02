<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

use Core\Contracts\Migration;

/**
 * Class CreateUsers
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class CreateUsers extends Migration
{
    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @return void
     * @throws \Exception
     *
     * @see http://docs.phinx.org/en/latest/migrations.html
     */
    public function up()
    {
        $this->table($this->table)
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('email', 'string', ['limit' => 150])
            ->addColumn('password', 'string', ['limit' => 200])
            ->addTimestamps()
            ->addColumn('status', 'enum', [
                'values' => ['online', 'offline'],
                'default' => 'online',
            ])
            ->addIndex('email', ['unique' => true])
            ->save();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function down()
    {
        parent::down();
    }
}
