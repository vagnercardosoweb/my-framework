<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controllers\Controller;
use Core\Env;
use Core\Helpers\Path;

/**
 * Class BitbucketController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class BitbucketController extends Controller
{
    /**
     * [POST] /api/deploy/bitbucket.
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        // Token
        $token = $this->getParams('token');
        $deployToken = Env::get('DEPLOY_KEY', Env::get('DEPLOY_TOKEN', null));

        if ($token !== $deployToken) {
            throw new \InvalidArgumentException('Token invalid.');
        }

        // Body
        $body = json_decode(file_get_contents('php://input'), true);

        if (empty($body['push']['changes'])) {
            throw new \Exception('Body push changes empty.');
        }

        if (($countChange = count($body['push']['changes'])) > 0) {
            $lastChange = $body['push']['changes'][$countChange - 1]['new'];

            // Verify type
            if ('branch' !== $lastChange['type']) {
                throw new \Exception('Type change new diff branch.');
            }

            // Muda o diretório para a raiz
            chdir(Path::root());

            // Verifica pasta .git
            if (!file_exists(Path::root('/.git'))) {
                throw new \Exception('Git not initialize.');
            }

            switch ($lastChange['name']) {
                case 'master':
                    `git fetch origin && git reset --hard origin/master 2>&1`;
                    break;
                default:
                    throw new \Exception('Branch undefined.');
            }
        } else {
            throw new \Exception('Count change less than 0.');
        }

        return $this->jsonSuccess('Deploy bitbucket successfully.');
    }
}
