<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
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
     * Class SessionProvider.
     *
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class SessionProvider extends Provider
    {
        public function register()
        {
            $this->container['session'] = function () {
                if (!Helper::isPhpCli() && true == env('APP_SESSION', 'true')) {
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

        public function boot()
        {
            if (!Helper::isPhpCli() && 'true' == env('APP_SESSION', 'true')) {
                $this->view->addGlobal('session', $this->session->all());
                $this->view->addGlobal('flash', $this->flash->all());
            }
        }
    }
}
