<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */
use App\Models\User;
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
     */
    public function run()
    {
        try {
            // Prevent duplicates
            if ('mysql' == $this->getAdapter()->getOption('adapter')) {
                $this->table('users')->truncate();
            }

            // Create users
            for ($i = 1; $i <= 10; ++$i) {
                $user = new User();
                $user->data([
                    'name' => "User {$i}",
                    'email' => "user{$i}@email.com",
                    'password' => $user->password->hash('password'),
                    'status' => ['online', 'offline'][rand(0, 1)],
                ])->save();
            }
        } catch (Exception $e) {
            die("ERROR: {$e->getMessage()}");
        }
    }
}
