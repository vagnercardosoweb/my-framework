<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\Encryption;

/**
 * Class EncryptionProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class EncryptionProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['encryption'] = function () {
            return new Encryption(
                (env('APP_KEY', null)
                    ?: md5(md5(
                            sprintf('vcw-%s', $_SERVER['HTTP_HOST'] ?? 'VCWebNetworks'))
                    ))
            );
        };
    }
}
