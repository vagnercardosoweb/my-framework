<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 29/12/2019 Vagner Cardoso
 */

namespace App\Models;

use Core\Database\Model;

/**
 * Class BaseModel.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class BaseModel extends Model
{
    /**
     * @return string
     */
    public function tb(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function pk(): string
    {
        return $this->primaryKey;
    }

    /**
     * @return string
     */
    public function fk(): string
    {
        return $this->foreignKey;
    }

    /**
     * @param array|object $rows
     *
     * @return array
     */
    public function toAllCamelCase($rows)
    {
        // CORRIGIR ESSE MÉTODO PORQUE ESTÁ CONVERTENDO A CLASSE TODA AO INVEZ DE APENAS OS DADOS
        $newRows = [];

        foreach ($rows as $index => $row) {
            foreach ($row as $column => $value) {
                if (is_array($value) && !empty($value[0])) {
                    $newRows[$index][$this->columnCamelCase($column)] = $this->toAllCamelCase($value);
                } elseif (is_array($value) || is_object($value)) {
                    $newRows[$index][$this->columnCamelCase($column)] = $this->toCamelCase($value);
                } else {
                    $newRows[$index][$this->columnCamelCase($column)] = $value;
                }
            }
        }

        return $newRows;
    }

    /**
     * @param mixed|null $data
     *
     * @return array
     */
    public function toCamelCase($data = null)
    {
        if (is_null($data)) {
            $data = $this->data;
        }

        if ($data instanceof Model) {
            $data = $data->data;
        }

        $newData = [];

        foreach ($data as $column => $value) {
            if (is_array($value) || is_object($value)) {
                $newData[$this->columnCamelCase($column)] = $this->toCamelCase($value);
            } else {
                $newData[$this->columnCamelCase($column)] = $value;
            }
        }

        return $newData;
    }

    /**
     * @param string $column
     *
     * @return string
     */
    protected function columnCamelCase($column)
    {
        if (false !== strpos($column, '_')) {
            $column = ucwords(str_replace('_', ' ', strtolower($column)));
            $column = str_replace(' ', '', lcfirst($column));
        }

        return $column;
    }
}
