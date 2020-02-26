<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 26/02/2020 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controllers\Controller;
use Core\Helpers\Path;

/**
 * Class GitlabController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class GitlabController extends Controller
{
    /**
     * [POST] /api/deploy/gitlab.
     *
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function post()
    {
        // Headers
        $token = $this->request->getHeaderLine('X-Gitlab-Token');
        $event = $this->request->getHeaderLine('X-Gitlab-Event');

        if (empty($token) || $token !== env('DEPLOY_KEY')) {
            throw new \InvalidArgumentException('Token invalid.');
        }

        // Body
        $body = json_decode(file_get_contents('php://input'), true);

        if (empty($body['ref'])) {
            throw new \Exception('Body ref empty.');
        }

        // Trata branch
        list(, , $branch) = explode('/', $body['ref']);

        // Muda o diretório para a raiz
        chdir(Path::root());

        // Verifica pasta .git
        if (!file_exists(Path::root('/.git'))) {
            throw new \Exception('Git not initialize.');
        }

        switch ($branch) {
            case 'master':
                `git fetch origin && git reset --hard origin/master 2>&1`;
                break;
            default:
                throw new \Exception('Branch undefined.');
        }

        return $this->jsonSuccess('Deploy gitlab successfully.');
    }
}
