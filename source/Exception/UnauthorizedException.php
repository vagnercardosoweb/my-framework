<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace Core\Exception;

/**
 * Class UnauthorizedException.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class UnauthorizedException extends Exception
{
    /**
     * UnauthorizedException constructor.
     *
     * @param string|null $message
     * @param int         $statusCode
     */
    public function __construct(string $message = 'Unauthorized', int $statusCode = 401)
    {
        parent::__construct($message, E_USER_ERROR, $statusCode);
    }
}
