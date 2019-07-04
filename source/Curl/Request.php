<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace Core\Curl;

use Core\Helpers\Helper;

/**
 * Class Curl.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Request
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return \Core\Curl\Response
     */
    public function get(string $endPoint, $params = []): Response
    {
        return $this->create('get', $endPoint, $params);
    }

    /**
     * @param string       $endPoint
     * @param array|string $params
     *
     * @throws \Exception
     *
     * @return \Core\Curl\Response
     */
    public function post(string $endPoint, $params = []): Response
    {
        return $this->create('post', $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return \Core\Curl\Response
     */
    public function put(string $endPoint, $params = []): Response
    {
        return $this->create('put', $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return \Core\Curl\Response
     */
    public function delete(string $endPoint, $params = []): Response
    {
        return $this->create('delete', $endPoint, $params);
    }

    /**
     * @param string       $method
     * @param string       $endPoint
     * @param array|string $params
     *
     * @return \Core\Curl\Response
     */
    public function create(string $method, string $endPoint, $params = null): Response
    {
        $method = mb_strtoupper($method, 'UTF-8');
        $params = is_array($params) ? Helper::httpBuildQuery($params) : ($params ?: null);

        if ('GET' === $method) {
            $separator = false !== strpos($endPoint, '?') ? '&' : '?';
            $endPoint .= "{$separator}{$params}";
        }

        // Init curl
        $curl = curl_init($endPoint);

        // Mount options
        $defaultOptions = [
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ('GET' !== $method) {
            $defaultOptions[CURLOPT_POSTFIELDS] = $params;
        }

        if ('POST' === $method) {
            $defaultOptions[CURLOPT_POST] = true;
        }

        if ('PUT' === $method) {
            $defaultOptions[CURLOPT_PUT] = true;
        }

        // Merge options
        $newOptions = $this->getOptions();
        curl_setopt_array($curl, (array_diff_key($defaultOptions, $newOptions) + $newOptions));

        // Results
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_error($curl);
        curl_close($curl);

        return new Response(
            $response, $info, $error
        );
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];

        foreach ($this->headers as $key => $value) {
            $headers[] = "{$key}: {$value}";
        }

        return $headers;
    }

    /**
     * @param string|array $keys
     * @param mixed        $value
     *
     * @return Request
     */
    public function setHeaders($keys, $value = null): Request
    {
        if (!is_array($keys)) {
            $keys = [$keys => $value];
        }

        foreach ($keys as $k => $v) {
            $this->headers[$k] = $v;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string|array $options
     * @param mixed        $value
     *
     * @return Request
     */
    public function setOptions($options, $value = null): Request
    {
        if (!is_array($options)) {
            $options = [$options => $value];
        }

        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }

        return $this;
    }
}
