<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\View;
use Twig\Extension\DebugExtension;

/**
 * Class ViewProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ViewProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'view';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            $view = new View(
                Config::get('view.templates'),
                Config::get('view.options')
            );

            $view->addExtension(new DebugExtension());

            foreach (Config::get('view.registers') as $key => $items) {
                foreach ($items as $name => $item) {
                    if ('functions' == $key) {
                        $view->addFunction($name, $item);
                    } elseif ('filters' == $key) {
                        $view->addFilter($name, $item);
                    }
                }
            }

            return $view;
        };
    }
}
