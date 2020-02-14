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

use Core\App;

/**
 * Class Event.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Event
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        return App::getInstance()->resolve($name);
    }
}
