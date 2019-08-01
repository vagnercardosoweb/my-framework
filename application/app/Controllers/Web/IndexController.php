<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 01/08/2019 Vagner Cardoso
 */

namespace App\Controllers\Web;

use App\Controller\BaseController;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends BaseController
{
    /**
     * [GET] /.
     *
     * @return \Slim\Http\Response
     */
    public function index()
    {
        return $this->view('@web.index');
    }
}
