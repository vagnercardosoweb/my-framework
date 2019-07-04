<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core;

/**
 * Class Event.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Event
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var Event
     */
    private static $instance;

    /**
     * @return Event
     */
    public static function getInstance(): Event
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string   $event
     * @param callable $callable
     * @param int      $priority
     *
     * @return void
     */
    public function on(string $event, callable $callable, int $priority = 10): void
    {
        $event = (string)$event;
        $priority = (int)$priority;

        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }

        if (is_callable($callable)) {
            $this->events[$event][$priority][] = $callable;
        }
    }

    /**
     * @param string $event
     * @param ...    $params
     *
     * @return mixed
     */
    public function emit(string $event)
    {
        $event = (string)$event;

        if (!isset($this->events[$event])) {
            $this->events[$event] = [[]];
        }

        if (!empty($this->events[$event])) {
            if (count($this->events[$event]) > 1) {
                ksort($this->events[$event]);
            }

            $params = func_get_args();
            array_shift($params);
            $executed = [];

            foreach ($this->events[$event] as $priority) {
                if (!empty($priority)) {
                    foreach ($priority as $callable) {
                        $executed[] = call_user_func_array(
                            $callable, $params
                        );
                    }
                }
            }

            return array_shift($executed);
        }
    }

    /**
     * @param string $event
     *
     * @return mixed
     */
    public function events(?string $event = null)
    {
        if (!empty($event)) {
            return isset($this->events[$event])
                ? $this->events[$event]
                : null;
        }

        return $this->events;
    }

    /**
     * @param string $event
     *
     * @return void
     */
    public function clear(?string $event = null): void
    {
        $event = (string)$event;

        if (!empty($event) && isset($this->events[$event])) {
            $this->events[$event] = [[]];
        } else {
            foreach ($this->events as $key => $value) {
                $this->events[$key] = [[]];
            }
        }
    }
}
