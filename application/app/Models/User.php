<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 04/07/2019 Vagner Cardoso
 */

namespace App\Models;

use Core\Helpers\Validate;

/**
 * Class User.
 *
 * @author  Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @param array $data
     * @param bool  $validate
     *
     * @throws \Exception
     */
    protected function _data(array &$data, $validate)
    {
        // Where
        $where = !empty($this->getPrimaryValue())
            ? sprintf('AND %s.%s != "%s"', $this->table, $this->primaryKey, $this->getPrimaryValue())
            : null;

        // Validate
        if ($validate) {
            Validate::rules($data, [
                'name!!' => ['required' => 'Nome não pode ser vázio.'],
                'email!!' => [
                    'email' => 'O E-mail informado não é válido.',
                    'databaseNotExists' => [
                        'message' => 'O e-mail digitado já foi registrado.',
                        'params' => [$this->table, 'email', $where],
                    ],
                ],
                'password' => [
                    'required' => [
                        'message' => 'Senha não pode ser vázio.',
                        'check' => empty($data['id']),
                    ],
                ],
            ]);
        }

        // Password
        if (!empty($data['password'])) {
            $data['password'] = $this->password->hash($data['password']);
        }
    }
}
