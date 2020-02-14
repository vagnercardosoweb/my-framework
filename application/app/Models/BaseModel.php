<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @link https://github.com/vagnercardosoweb
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 13/02/2020 Vagner Cardoso
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
