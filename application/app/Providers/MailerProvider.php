<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 07/06/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Config;
use Core\Mailer\Mailer;

/**
 * Class MailerProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class MailerProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'mailer';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Mailer([
                'debug' => Config::get('mail.debug', 0),
                'charset' => Config::get('mail.charset', null),
                'auth' => Config::get('mail.auth', true),
                'secure' => Config::get('mail.secure', 'tls'),
                'host' => Config::get('mail.host', null),
                'port' => Config::get('mail.port', 587),
                'username' => Config::get('mail.username', null),
                'password' => Config::get('mail.password', null),
                'from' => [
                    'name' => Config::get('mail.from.name', null),
                    'mail' => Config::get('mail.from.mail', null),
                ],
                'language' => [
                    'code' => Config::get('mail.language.code', null),
                    'path' => Config::get('mail.language.path', null),
                ],
            ]);
        };
    }
}
