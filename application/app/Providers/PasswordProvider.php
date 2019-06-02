<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {

    use Core\Password\Argon;
    use Core\Password\Argon2Id;
    use Core\Password\Bcrypt;

    /**
     * Class PasswordProvider
     *
     * @package App\Providers
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class PasswordProvider extends Provider
    {
        /**
         * @return void
         */
        public function register()
        {
            $this->container['password'] = function () {
                switch (env('APP_PASSWORD_DRIVER', 'bcrypt')) {
                    case 'bcrypt':
                        return new Bcrypt();
                        break;

                    case 'argon':
                        return new Argon();
                        break;

                    case 'argon2id':
                        return new Argon2Id();
                        break;
                }
            };
        }
    }
}
