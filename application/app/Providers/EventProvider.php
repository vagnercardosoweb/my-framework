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

use Core\Event;
use Pimple\Container;

/**
 * Class EventProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class EventProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'event';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function (Container $container) {
            $event = Event::getInstance();

            if ($container->offsetExists('view')) {
                $container['view']->addFunction('event_emit', [$event, 'emit']);
                $container['view']->addFunction('event_has', [$event, 'events']);
            }

            return $event;
        };
    }
}
