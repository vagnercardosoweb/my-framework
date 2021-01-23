<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 23/01/2021 Vagner Cardoso
 */

namespace App\Providers;

use Core\Encryption;
use Core\Env;

/**
 * Class EncryptionProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class EncryptionProvider extends Provider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'encryption';
    }

    /**
     * @return \Closure
     */
    public function register(): \Closure
    {
        return function () {
            return new Encryption($this->generateKey());
        };
    }

    /**
     * @return string
     */
    protected function generateKey(): string
    {
        $appKey = Env::get('APP_KEY', null);

        if (empty($appKey)) {
            $host = $_SERVER['HTTP_HOST'] ?? 'vc:key';
            $appKey = hash('sha256', sprintf('vcw:%s', $host));
        }

        return $appKey;
    }
}
