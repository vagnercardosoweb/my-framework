<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Helpers\Validate;

/**
 * Class Response.
 *
 * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Response
{
    /**
     * @var mixed
     */
    private $body;

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
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        if (is_array($this->body) && isset($this->body[$name])) {
            return $this->body[$name];
        }

        if (is_object($this->body) && isset($this->body->{$name})) {
            return $this->body->{$name};
        }

        return null;
    }

    /**
     * @return object|null
     */
    public function getError(): ?object
    {
        return $this->error;
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
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return object|null
     */
    public function getInfo(): ?object
    {
        return $this->info;
    }

    /**
     * @return bool
     */
    public function isClientError(): bool
    {
        return $this->getStatus() >= 400 && $this->getStatus() < 500;
    }

    /**
     * @return bool
     */
    public function isServerError(): bool
    {
        return $this->getStatus() >= 500 && $this->getStatus() < 600;
    }

    /**
     * @param string $body
     *
     * @return object|string|null
     */
    private function buildBody(string $body)
    {
        return Validate::json($body) ?? Validate::xml($body) ?? ($body ?: null);
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
}
