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
use Core\Helpers\Str;

/**
 * Class ModalDetailController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class ModalDetailController extends Controller
{
    /**
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function index()
    {
        $post = $this->getParamsFiltered();
        $post['divId'] = $post['divId'] ?? 'modalContent';

        if (empty($post['view'])) {
            throw new \Exception('Você deve passar a view para inserir na modal.');
        }

        if (!empty($post['model']) && (!empty($post['id']) && $post['id'] > 0)) {
            $model = '\\App\\Models\\'.Str::studly($post['model']);

            if (!$post['row'] = (new $model())->reset()->fetchById($post['id'])) {
                throw new \Exception('Registro não encontrado.');
            }
        }

        return $this->jsonSuccess([
            'object' => [$post['divId'] => $this->view->fetch($post['view'], $post)],
        ]);
    }
}
