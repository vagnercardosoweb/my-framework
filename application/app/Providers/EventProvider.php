<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
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
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['event'] = function () {
            return Event::getInstance();
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function boot(): void
    {
        $this->view->addFunction('event_emit', function ($event) {
            $params = func_get_args();
            array_shift($params);

            return $this->event->emit($event, ...$params);
        });

        $this->view->addFunction('event_has', function (string $event) {
            return $this->event->events($event);
        });
    }
}
