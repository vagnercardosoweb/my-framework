<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Session\Session;
use Pimple\Container;

/**
 * Class SessionProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class SessionProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'session';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function (Container $container) {
            $session = new Session();

            if ($container->offsetExists('view')) {
                $container['view']->addGlobal('session', $session);
            }

            return $session;
        };
    }
}
