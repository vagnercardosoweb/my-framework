<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Controllers\Api;

use App\Controller\Controller;
use Core\App;
use Core\Curl\Request;
use Core\Helpers\Str;

/**
 * Class UtilController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class UtilController extends Controller
{
    /**
     * @param string $name
     * @param mixed  $arguments
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function __call($name, $arguments)
    {
        try {
            if (in_array($name, ['options', 'patch'])) {
                return $this->response;
            }

            if (!method_exists(App::class, $name)) {
                throw new \Exception(
                    'Invalid requisition method.', E_USER_ERROR
                );
            }

            $method = Str::camel(str_replace('/', '-', $arguments[0]));
            $path = (!empty($arguments[1]) ? $arguments[1] : '');
            $data = array_merge(($path ? explode('/', $path) : []), $this->getParamsFiltered());

            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(
                    sprintf('Call to undefined method %s::%s()', get_class($this), $method), E_ERROR
                );
            }

            return $this->{$method}($data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * [GET|POST] /api/zipcode/{zipcode}.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    protected function zipcode(array $data)
    {
        try {
            if (!empty($data[0])) {
                $data['cep'] = $data[0];
            }

            if (empty($data['cep'])) {
                throw new \InvalidArgumentException(
                    'Você deve passar o CEP para buscar.', E_USER_ERROR
                );
            }

            if (strlen(preg_replace('/[^0-9]/', '', $data['cep'])) < 8) {
                throw new \InvalidArgumentException(
                    "O CEP {$data['cep']} informado deve conter, no mínimo 8 números.", E_USER_ERROR
                );
            }

            // Find cep...
            $response = (new Request())->get("https://viacep.com.br/ws/{$data['cep']}/json");
            $cep = $response->getBody();

            if (!empty($cep->erro) || $response->getError()) {
                throw new \Exception(
                    "O CEP {$data['cep']} informado não foi encontrado.", E_USER_ERROR
                );
            }

            // Format address
            $cep->endereco = "{$cep->logradouro} - {$cep->bairro}, {$cep->localidade} - {$cep->uf}, {$data['cep']}, Brasil";

            // Google Maps
            if ($googleMapsKey = env('GOOGLE_MAPS_KEY', null)) {
                $map = (new Request())->get('https://maps.google.com/maps/api/geocode/json', [
                    'key' => $googleMapsKey,
                    'sensor' => true,
                    'address' => urlencode($cep->endereco),
                ])->getBody();

                if ('OK' === $map->status && !empty($map->results[0])) {
                    $location = $map->results[0]->geometry->location;
                    $cep->latitude = (string)$location->lat;
                    $cep->longitude = (string)$location->lng;
                }
            }

            return json($cep);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * [POST] /api/modal-detail.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    protected function modalDetail(array $data)
    {
        try {
            if (empty($data['view'])) {
                throw new \Exception(
                    'Você deve passar a view para inserir na modal.', E_USER_ERROR
                );
            }

            if (!empty($data['model']) && (!empty($data['id']) && $data['id'] > 0)) {
                $model = '\\App\\Models\\'.Str::studly($data['model']);

                if (!$data['row'] = (new $model())->reset()->fetchById($data['id'])) {
                    throw new \Exception(
                        'Registro não encontrado.', E_USER_ERROR
                    );
                }
            }

            return json([
                'object' => [
                    'modalContent' => $this->view->fetch($data['view'], $data),
                ],
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
