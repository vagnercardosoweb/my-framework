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

/**
 * Class PhpErrorProvider.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class PhpErrorProvider extends ErrorProvider
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'phpErrorHandler';
    }
}
