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

use Core\Session\Flash;
use Pimple\Container;

/**
 * Class FlashProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class FlashProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'flash';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function (Container $container) {
            $flash = new Flash();

            if ($container->offsetExists('view')) {
                $container['view']->addGlobal('flash', $flash);
            }

            return $flash;
        };
    }
}
