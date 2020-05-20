<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
 */

use App\Models\UserModel;
use Core\Password\PasswordFactory;
use Phinx\Seed\AbstractSeed;

/**
 * Class UsersSeeder.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class UsersSeeder extends AbstractSeed
{
    /**
     * @throws \Exception
     *
     * @return void
     */
    public function run(): void
    {
        try {
            if ('mysql' == $this->getAdapter()->getOption('adapter')) {
                $this->table('users')->truncate();
            }

            for ($i = 1; $i <= 10; $i++) {
                (new UserModel())->create([
                    'name' => "User {$i}",
                    'email' => "user{$i}@email.com",
                    'password' => PasswordFactory::create()->make('password'),
                    'status' => ['online', 'offline'][rand(0, 1)],
                ]);
            }
        } catch (Exception $e) {
            die("ERROR: {$e->getMessage()}");
        }
    }
}
