<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

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
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['view'] = function () {
            return new View(
                config('view.templates'),
                config('view.options')
            );
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function boot(): void
    {
        $this->view->addExtension(new DebugExtension());

        foreach (config('view.registers') as $key => $items) {
            foreach ($items as $name => $item) {
                switch ($key) {
                    case 'functions':
                        $this->view->addFunction($name, $item);
                        break;

                    case 'filters':
                        $this->view->addFilter($name, $item);
                        break;
                }
            }
        }
    }
}
