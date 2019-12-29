<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
 */

namespace App\Events;

/**
 * Class TestEvent.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class TestEvent extends Event
{
    /**
     * @param mixed $data
     */
    public function __invoke($data)
    {
        dd($data);
    }
}
