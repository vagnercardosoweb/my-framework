<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

namespace Core\Interfaces;

/**
 * Class ServiceProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
interface ServiceProvider
{
    /**
     * @return string|array
     */
    public function name();

    /**
     * @return \Closure
     */
    public function register(): \Closure;
}
