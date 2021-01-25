<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Database\Database;
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
        Database::setEvent($event);

        if ($this->view) {
            $this->view->addFunction('event_emit', [$event, 'emit']);
            $this->view->addFunction('event_has', [$event, 'events']);
        }

        return function () use ($event) {
            return $event;
        };
    }
}
