<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 05/07/2020 Vagner Cardoso
 */

namespace App\Models;

use Core\Helpers\Validate;

/**
 * Class UserModel.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class UserModel extends BaseModel
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
     * @param \App\Models\UserModel $row
     *
     * @return void
     */
    protected function _row(UserModel $row)
    {
        // AQUI PASSA CADA LINHA DOS RESULTADOS
        // DAS QUERYS, APENAS RESULTADOS DE LEITURA
    }

    /**
     * @return void
     */
    protected function _conditions()
    {
        // AQUI É EXECUTADO ANTES DE TODAS LEITURAS
        // PODENDO ADICIONAR CONDIÇÕES PADRÕES, RELACIONAMENTO
        // ETC...
    }

    /**
     * @param array $data
     * @param bool  $validate
     *
     * @throws \Exception
     */
    protected function _data(array &$data, $validate)
    {
        // AQUI PASSA TODOS DADOS DE ENTRADA, UPDATE, SAVE, CREATE
        // PODENDO AQUI FAZER VERIFICAÇÕES NO BANCO, ADICIONAR CAMPOS DEFAULT,
        // TRATAMENTO DOS DADOS E MUITAS OUTRAS COISAS QUE QUEIRA IMPLEMENTAR
        // E TRATAR ANTES DE ENVIAR REALMENTE PRO BANCO DE DADOS.

        // ============================================================ //
        // Where
        $where = !empty($data[$this->primaryKey])
            ? sprintf('AND %s.%s != "%s"', $this->table, $this->primaryKey, $data[$this->primaryKey])
            : null;

        // Validate
        Validate::rules($data, [
            'name' => ['required' => 'Nome não pode ser vázio.'],
            'email' => [
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
        ], true, $validate);

        // Password
        if (!empty($data['password']) && 'unknown' === $this->hash->info($data['password'])['algoName']) {
            $data['password'] = $this->hash->make($data['password']);
        }
    }
}
