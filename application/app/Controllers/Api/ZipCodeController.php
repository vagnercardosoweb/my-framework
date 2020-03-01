<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Controllers\Api;

use App\Controllers\Controller;

/**
 * Class ZipCodeControllerController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ZipCodeController extends Controller
{
    /**
     * @param string $zipCode
     *
     * @throws \Exception
     *
     * @return object
     */
    public function index(string $zipCode)
    {
        if (empty($zipCode)) {
            throw new \InvalidArgumentException('Você deve passar o CEP para buscar.');
        }

        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);

        if (strlen($zipCode) < 8) {
            throw new \InvalidArgumentException("O CEP {$zipCode} informado deve conter, no mínimo 8 números.");
        }

        // Find cep...
        $response = $this->curl->get("https://viacep.com.br/ws/{$zipCode}/json");
        $body = $response->getBody();

        if (!empty($body->erro) || $response->getError()) {
            throw new \Exception("O CEP {$zipCode} informado não foi encontrado.");
        }

        // Format address
        $body->endereco = "{$body->logradouro} - {$body->bairro}, {$body->localidade} - {$body->uf}, {$zipCode}, Brasil";

        // Google Maps
        if ($googleMapsKey = env('GOOGLE_GEOCODE_API_KEY', null)) {
            $map = $this->curl->get('https://maps.google.com/maps/api/geocode/json', [
                'key' => $googleMapsKey,
                'sensor' => true,
                'address' => urlencode($body->endereco),
            ])->getBody();

            if ('OK' === $map->status && !empty($map->results[0])) {
                $location = $map->results[0]->geometry->location;
                $body->latitude = (string)$location->lat;
                $body->longitude = (string)$location->lng;
            }
        }

        return $body;
    }
}