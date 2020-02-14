<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\App;
use Core\Session\Flash;
use Core\Session\Session;

/**
 * Class SessionProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SessionProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['session'] = function () {
            if (!App::isCli() && 'true' == env('APP_SESSION', true)) {
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
     * {@inheritdoc}
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->session) {
            $this->view->addGlobal('session', $this->session);
            $this->view->addGlobal('flash', $this->flash);
        }
    }
}
