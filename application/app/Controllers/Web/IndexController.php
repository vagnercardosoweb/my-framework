<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Controllers\Web;

use App\Controller\Controller;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends Controller
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
