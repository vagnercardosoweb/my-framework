<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace App\Controllers\Console;

use App\Controllers\Controller;
use Core\Helpers\Path;

/**
 * Class IntroController.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class IntroController extends Controller
{
    /**
     * @throws \Exception
     *
     * @return string
     */
    public function index()
    {
        return sprintf("Usage: php %s/console.php \"/route-name?a=1&b=2\" \"get|post|delete|put\"\n", Path::app());
    }
}
