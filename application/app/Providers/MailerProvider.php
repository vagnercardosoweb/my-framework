<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Providers;

use Core\Mailer\Mailer;

/**
 * Class MailerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MailerProvider extends Provider
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function register(): void
    {
        $this->container['mailer'] = function () {
            return new Mailer([
                'debug' => config('mail.debug', 0),
                'charset' => config('mail.charset', null),
                'auth' => config('mail.auth', true),
                'secure' => config('mail.secure', 'tls'),
                'host' => config('mail.host', null),
                'post' => config('mail.post', 587),
                'username' => config('mail.username', null),
                'password' => config('mail.password', null),
                'from' => [
                    'name' => config('mail.from.name', null),
                    'mail' => config('mail.from.mail', null),
                ],
                'language' => [
                    'code' => config('mail.language.code', null),
                    'path' => config('mail.language.path', null),
                ],
            ]);
        };
    }
}
