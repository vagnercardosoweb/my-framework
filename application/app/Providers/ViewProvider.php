<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {

    use Core\View;
    use Twig\Extension\DebugExtension;

    /**
     * Class ViewProvider
     *
     * @package App\Providers
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class ViewProvider extends Provider
    {
        /**
         * @return void
         */
        public function register()
        {
            $this->container['view'] = function () {
                return new View(
                    config('twig.templates'), config('twig.options')
                );
            };
        }

        /**
         * @return void
         */
        public function boot()
        {
            $this->view->addExtension(new DebugExtension());

            foreach (config('twig.registers') as $key => $items) {
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
}
