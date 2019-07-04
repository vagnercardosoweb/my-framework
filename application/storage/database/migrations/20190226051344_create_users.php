<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

use Core\Phinx\Migration;

/**
 * Class CreateUsers.
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
     * @throws \Exception
     *
     * @return void
     *
     * @see http://docs.phinx.org/en/latest/migrations.html
     */
    public function up(): void
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
            ->save()
        ;
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function down(): void
    {
        $this->table($this->table)->drop()->save();
    }
}
