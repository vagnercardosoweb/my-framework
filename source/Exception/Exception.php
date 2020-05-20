<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 20/05/2020 Vagner Cardoso
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
     * @param int|string $code
     *
     * @return string
     */
    public static function getErrorType($code)
    {
        if (is_string($code) && E_USER_SUCCESS !== $code) {
            $code = E_USER_ERROR;
        }

        switch ($code) {
            case E_USER_NOTICE:
            case E_NOTICE:
                $result = 'info';
                break;

            case E_USER_WARNING:
            case E_WARNING:
                $result = 'warning';
                break;

            case E_USER_ERROR:
            case E_ERROR:
            case '0':
                $result = 'danger';
                break;

            case E_USER_SUCCESS:
                $result = 'success';
                break;

            default:
                $result = 'danger';
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
