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

use Core\Session\Flash;
use Core\Session\Session;

/**
 * Class FlashProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class FlashProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'flash';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        $flash = null;

        if ($this->session instanceof Session) {
            $flash = new Flash();

            if ($this->view) {
                $this->view->addGlobal('flash', $flash);
            }
        }

        return function () use ($flash) {
            return $flash;
        };
    }
}
