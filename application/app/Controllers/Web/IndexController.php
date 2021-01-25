<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace App\Controllers\Web;

use App\Controllers\Controller;
use Slim\Http\Response;

/**
 * Class IndexController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IndexController extends Controller
{
    /**
     * @throws \Exception
     *
     * @return \Slim\Http\Response
     */
    public function index(): Response
    {
        return $this->view('@web');
    }
}
