<?php

/**
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>.
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

return [
    /*
     * 0 = Off ( Em produção manter em off )
     * 1 = Mensagem cliente
     * 2 = Mensagem do servidor e cliente
     */

    'debug' => env('MAIL_DEBUG', 0),

    /*
     * Charset
     *
     * Codigicação das palavras
     */

    'charset' => env('MAIL_CHARSET', 'utf-8'),

    /*
     * Host
     *
     * Servidor que irá envia o e-mail
     */

    'host' => env('MAIL_HOST', null),

    /*
     * Port
     *
     * Porta do servidor
     */

    'port' => env('MAIL_PORT', 587),

    /*
     * Username
     *
     * Usuário do servidor
     */

    'username' => env('MAIL_USER', null),

    /*
     * Password
     *
     * Senha do usuário do servidor
     */

    'password' => env('MAIL_PASS', null),

    /*
     * Auth
     *
     * Define se os e-mail serão autenticado
     */

    'auth' => env('MAIL_AUTH', true),

    /*
     * Secure
     *
     * Define o nível de segurança
     *
     * tls | ssl
     */

    'secure' => env('MAIL_SECURE', 'tls'),

    /*
     * From
     *
     * Remetente que irá envia os e-mails
     */

    'from' => [
        'name' => env('MAIL_FROM_NAME', ''),
        'mail' => env('MAIL_FROM_MAIL', ''),
    ],
];
