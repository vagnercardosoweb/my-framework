<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 22/02/2020 Vagner Cardoso
 */

namespace Core\Exception;

/**
 * Class ResponseException.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ResponseException extends Exception
{
    /**
     * ResponseException constructor.
     *
     * @param string $message
     * @param int    $statusCode
     */
    public function __construct(string $message, int $statusCode = 400)
    {
        parent::__construct($message, E_USER_ERROR, $statusCode);
    }
}
