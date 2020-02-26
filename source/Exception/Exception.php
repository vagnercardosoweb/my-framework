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
 * Class Exception.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Exception extends \Exception
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * AppException constructor.
     *
     * @param string|null $message
     * @param int         $code
     * @param int         $statusCode
     */
    public function __construct(string $message = null, $code = 0, int $statusCode = 400)
    {
        if (is_string($code) || !$code) {
            $code = E_USER_ERROR;
        }

        parent::__construct($message, $code);

        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
