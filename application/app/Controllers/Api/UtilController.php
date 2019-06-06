<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Controllers\Api {
    use App\Controller\Controller;
    use Core\App;
    use Core\Helpers\Curl;
    use Core\Helpers\Str;

    /**
     * Class UtilController
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
            if (!empty($name) && !empty($arguments)) {
                // Se for o método options retorna
                if (in_array($name, ['options', 'patch'])) {
                    return $this->response;
                }

                // Verifica se o méthod existe
                if (!method_exists(App::class, $name)) {
                    throw new \Exception(
                        'Invalid requisition method.', E_USER_ERROR
                    );
                }

                // Variáveis
                $method = Str::camel(str_replace('/', '-', $arguments[0]));
                $path = (!empty($arguments[1]) ? $arguments[1] : '');
                $data = array_merge(($path ? explode('/', $path) : []), request_params());

                // Veririca se o método existe
                if (!method_exists($this, $method)) {
                    throw new \BadMethodCallException(
                        sprintf('Call to undefined method %s::%s()', get_class($this), $method), E_ERROR
                    );
                }

                try {
                    return $this->{$method}($data);
                } catch (\Exception $e) {
                    throw $e;
                }
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

                // Busca cep
                $cep = (new Curl())->get("https://viacep.com.br/ws/{$data['cep']}/json");

                if (!empty($cep->erro)) {
                    throw new \Exception(
                        "O CEP {$data['cep']} informado não foi encontrado.", E_USER_ERROR
                    );
                }

                // Formata endereço
                $cep->endereco = "{$cep->logradouro} - {$cep->bairro}, {$cep->localidade} - {$cep->uf}, {$data['cep']}, Brasil";

                // Google Maps
                $map = (new Curl())->get('https://maps.google.com/maps/api/geocode/json', [
                    'key' => 'AIzaSyCUiWvcqkPMCH_CgTwbkOp74-9oEHlhMOA',
                    'sensor' => true,
                    'address' => urlencode($cep->endereco),
                ]);

                if ('OK' === $map->status && !empty($map->results[0])) {
                    $location = $map->results[0]->geometry->location;
                    $cep->latitude = (string)$location->lat;
                    $cep->longitude = (string)$location->lng;
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
                    $model = '\\App\\Models\\' . Str::studly($data['model']);

                    if (!$data['row'] = (new $model())->reset()->fetchById($data['id'])) {
                        throw new \Exception('Registro não encontrado.', E_USER_ERROR);
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
}
