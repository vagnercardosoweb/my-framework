<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 18/06/2019 Vagner Cardoso
 */

namespace Core\Helpers;

/**
 * Class Curl.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class Curl
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
     * @return mixed
     */
    public function get(string $endPoint, $params = [])
    {
        return $this->create('get', $endPoint, $params);
    }

    /**
     * @param string       $method
     * @param string       $endPoint
     * @param string|array $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function create(string $method, string $endPoint, $params = [])
    {
        try {
            $response = $this->createRequest($method, $endPoint, $params);

            if ($json = Helper::isJson($response)) {
                return $json;
            }

            if ($xml = Helper::isXml($response)) {
                return $xml;
            }

            return $response;
        } catch (\Exception $e) {
            throw $e;
        }
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
     * @return Curl
     */
    public function setHeaders($keys, $value = null): Curl
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
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string|array $options
     * @param mixed        $value
     *
     * @return Curl
     */
    public function setOptions($options, $value = null): Curl
    {
        if (!is_array($options)) {
            $options = [$options => $value];
        }

        foreach ($options as $k => $v) {
            $this->options[$k] = $v;
        }

        return $this;
    }

    /**
     * @param string       $endPoint
     * @param array|string $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function post(string $endPoint, $params = [])
    {
        return $this->create('post', $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function put(string $endPoint, $params = [])
    {
        return $this->create('put', $endPoint, $params);
    }

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function delete(string $endPoint, $params = [])
    {
        return $this->create('delete', $endPoint, $params);
    }

    /**
     * @param string       $method
     * @param string       $endPoint
     * @param array|string $params
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function createRequest(string $method, string $endPoint, $params)
    {
        $method = mb_strtoupper($method, 'UTF-8');

        // Verifica se os parametros foi passado
        if (!empty($params)) {
            // Formato de array
            if (is_array($params)) {
                $params = Helper::httpBuildQuery($params);
            } else {
                // Formato de json
                if (Helper::isJson($params) && 'GET' !== $method) {
                    $this->setHeaders('Content-Type', 'application/json');
                }
            }
        } else {
            $params = null;
        }

        // Trata a URL se for GET
        if ('GET' === $method) {
            $separator = '?';

            if (false !== strpos($endPoint, '?')) {
                $separator = '&';
            }

            $endPoint .= "{$separator}{$params}";
        }

        // Inicializa o cURL
        $curl = curl_init();

        // Monta as opções da requisição
        $options = [
            CURLOPT_URL => $endPoint,
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 80,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        // Verifica se não e GET e passa os parametros
        if ('GET' !== $method) {
            $options[CURLOPT_POSTFIELDS] = $params;
        }

        // Verifica se a requisição e POST
        if ('POST' === $method) {
            $options[CURLOPT_POST] = true;
        }

        // Passa os options para a requisição
        curl_setopt_array($curl, $options + $this->getOptions());

        // Resultados
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        // Verifica se houve erros
        if ($error) {
            return $error;
        }

        // Retorna a resposta da requisição
        return $response;
    }
}
