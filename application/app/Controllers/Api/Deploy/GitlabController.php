<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 08/01/2021 Vagner Cardoso
 */

namespace App\Controllers\Api\Deploy;

use App\Controllers\Controller;
use Core\Env;
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
        $deployToken = Env::get('DEPLOY_KEY', Env::get('DEPLOY_TOKEN', null));

        if (empty($token) || $token !== $deployToken) {
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
                shell_exec('git fetch origin && git reset --hard origin/master 2>&1');
                break;
            default:
                throw new \Exception('Branch undefined.');
        }

        return $this->jsonSuccess('Deploy gitlab successfully.');
    }
}
