<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/06/2021 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Helpers\Helper;
use Core\Helpers\Obj;
use JsonSerializable;

/**
 * Class Response.
 *
 * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Response implements JsonSerializable
{
    /**
     * @var mixed
     */
    private $body;

    /**
     * @var mixed
     */
    private $pureBody;

    /**
     * @var object
     */
    private $info;

    /**
     * @var object
     */
    private $error;

    /**
     * Response constructor.
     *
     * @param mixed $body
     * @param mixed $info
     * @param mixed $error
     */
    public function __construct($body, $info, $error)
    {
        $this->body = $this->buildBody($body);
        $this->info = (object)$info ?: null;
        $this->error = $this->buildError($error);
        $this->pureBody = $body;
    }

    /**
     * @param string $body
     *
     * @return object|string|null
     */
    private function buildBody(string $body)
    {
        if ($xml = Helper::parseXml($body)) {
            return $xml;
        }

        if ($json = Helper::decodeJson($body)) {
            return $json;
        }

        return $body;
    }

    /**
     * @param string $error
     *
     * @return object|null
     */
    private function buildError(string $error): ?object
    {
        if (empty($error) && (!$this->isClientError() && !$this->isServerError())) {
            return null;
        }

        return (object)[
            'error' => true,
            'status' => $this->getStatus(),
            'message' => $error ?: ($this->isServerError() ? 'Server error.' : 'Client error.'),
        ];
    }

    /**
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->getStatus() >= 400 && $this->getStatus() < 500;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return isset($this->info->http_code)
            ? (int)$this->info->http_code
            : null;
    }

    /**
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getStatus() >= 500 && $this->getStatus() < 600;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $body = Obj::fromArray($this->body);

        if (isset($body->{$name})) {
            return $body->{$name};
        }

        return null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        if (is_object($this->body)) {
            $this->body->{$name} = $value;
        }
    }

    /**
     * @return object|null
     */
    public function getError(): ?object
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getPureBody()
    {
        return $this->pureBody;
    }

    /**
     * @return object|null
     */
    public function getInfo(): ?object
    {
        return $this->info;
    }

    /**
     * @return mixed|object|string|null
     */
    public function jsonSerialize()
    {
        return $this->body;
    }
}
