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
        $event = Event::getInstance();

        if ($this->view) {
            $this->view->addFunction('event_emit', [$event, 'emit']);
            $this->view->addFunction('event_has', [$event, 'events']);
        }

        return function () use ($event) {
            return $event;
        };
    }
}
