<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Events;

/**
 * Class ExampleEvent.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ExampleEvent extends Event
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'test';
    }

    /**
     * @return mixed
     */
    public function register()
    {
        dd(func_get_args(), $this);
    }
}
