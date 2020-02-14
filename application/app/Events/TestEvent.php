<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
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
     * {@inheritdoc}
     */
    public function __invoke($data)
    {
        dd($data);
    }
}
