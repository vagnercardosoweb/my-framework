<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author    Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @copyright 31/05/2019 Vagner Cardoso
 */

namespace App\Models {
    use App\Core\Helpers\Validate;
    use Core\Database\Model;

    /**
     * Class User
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
            // Validações
            if ($validate) {
                validate_params($data, [
                    'name' => 'Nome não pode ser vázio.',
                    'email' => 'E-mail não pode ser vázio.',
                    'password' => [
                        'message' => 'Senha não pode ser vázio.',
                        'force' => empty($data['id']),
                    ],
                ]);
            }

            // E-mail
            if (!empty($data['email'])) {
                if (!Validate::mail($data['email'])) {
                    throw new \InvalidArgumentException(
                        'O E-mail informado não é válido.', E_USER_WARNING
                    );
                }

                if ($this->where("AND {$this->table}.email = '{$data['email']}'")->count() > 0) {
                    throw new \InvalidArgumentException(
                        'O e-mail digitado já foi registrado.', E_USER_WARNING
                    );
                }
            }
        }
    }
}
