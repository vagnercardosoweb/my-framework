<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Providers {

    use Core\Mailer\Mailer;

    /**
     * Class MailerProvider
     *
     * @package App\Providers
     * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
     */
    class MailerProvider extends Provider
    {
        /**
         * @inheritDoc
         */
        public function register()
        {
            $this->container['mailer'] = function () {
                return new Mailer();
            };
        }
    }
}
