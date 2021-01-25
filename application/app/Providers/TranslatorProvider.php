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

use Core\Env;
use Core\Translator;

/**
 * Class TranslatorProvider.
 */
class TranslatorProvider extends Provider
{
    /**
     * @return string[]
     */
    public function name(): array
    {
        return ['translator'];
    }

    /**
     * @throws \Exception
     *
     * @return \Closure
     */
    public function register(): \Closure
    {
        /** @var \Slim\Http\Request $request */
        $request = $this->container->get('request');
        $language = $request->getQueryParam('language') ?? $request->getHeaderLine('Accept-Language');

        Translator::setFallback(Env::get('APP_LOCALE', 'pt-br'));
        Translator::setLanguage($language);

        return function () {
            return new Translator();
        };
    }
}
