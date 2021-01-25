<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 25/01/2021 Vagner Cardoso
 */

namespace Core\Interfaces;

/**
 * Class ConnectionEvent.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
interface ConnectionEvent
{
    /**
     * @param \PDO $pdo
     *
     * @return mixed
     */
    public function __invoke(\PDO $pdo);
}
