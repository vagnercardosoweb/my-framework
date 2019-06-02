<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {

    use Core\Helpers\Helper;
    use Core\Session\Flash;
    use Core\Session\Session;

    /**
     * Class SessionProvider
     *
     * @package App\Providers
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class SessionProvider extends Provider
    {
        /**
         * @return void
         */
        public function register()
        {
            $this->container['session'] = function () {
                if (!Helper::isPhpCli() && env('APP_SESSION', 'true') == true) {
                    return new Session();
                }

                return false;
            };

            $this->container['flash'] = function () {
                if ($this->session) {
                    return new Flash();
                }

                return false;
            };
        }

        /**
         * @return void
         */
        public function boot()
        {
            if (!Helper::isPhpCli() && env('APP_SESSION', 'true') == 'true') {
                $this->view->addGlobal('session', $this->session->all());
                $this->view->addGlobal('flash', $this->flash->all());
            }
        }
    }
}
