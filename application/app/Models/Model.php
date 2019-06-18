<?php

/*
 * VCWeb Networks <https://www.vcwebnetworks.com.br/>
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 18/06/2019 Vagner Cardoso
 */

namespace App\Models;

use Core\Database\Model as DatabaseModel;
use Core\Helpers\Obj;

/**
 * Class Model.
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
abstract class Model extends DatabaseModel
{
    /**
     * @param \Closure $callback
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function transaction(\Closure $callback)
    {
        /** @var \Core\Database\Database $db */
        $db = $this->db->driver($this->driver);

        try {
            $db->beginTransaction();
            $callback = $callback($this);
            $db->commit();

            return $callback;
        } catch (\Exception $e) {
            $db->rollBack();

            throw $e;
        }
    }

    /**
     * @param array|object $data
     * @param bool         $validate
     *
     * @throws \Exception
     *
     * @return $this|object
     */
    public function save($data = [], bool $validate = true): object
    {
        $this->data($data, $validate);

        $data = $this->data;
        $where = implode(' ', $this->where);
        $bindings = $this->bindings;
        $primaryValue = $this->getPrimaryValue();

        $this->reset();
        $this->clear();

        if ($row = $this->fetchById($primaryValue)) {
            if (!empty($primaryValue)) {
                $where = "{$this->table}.{$this->getPrimaryKey()} = :pkid {$where}";
                $bindings['pkid'] = $primaryValue;
            }

            $row->data = $this->db->driver($this->driver)
                ->update(
                    $this->table, $data,
                    'WHERE '.$this->normalizeProperty($where),
                    $bindings
                )
            ;

            return $row;
        }

        $lastInsertId = $this->db->driver($this->driver)
            ->create($this->table, $data)
        ;

        if (!empty($lastInsertId)) {
            return $this->reset()
                ->where($where, $bindings)
                ->fetchById($lastInsertId)
            ;
        }

        return Obj::fromArray($data);
    }

    /**
     * @throws \Exception
     *
     * @return object|null
     */
    public function delete(): ?object
    {
        if (!empty($this->getPrimaryValue())) {
            $this->where[] = "AND {$this->table}.{$this->getPrimaryKey()} = :pkid ";
            $this->bindings['pkid'] = $this->getPrimaryValue();
        }

        if (is_array($this->where)) {
            $this->where = $this->normalizeProperty(
                implode(' ', $this->where)
            );
        }

        if (empty($this->where)) {
            throw new \InvalidArgumentException(
                sprintf('[delete] `%s::where()` is empty.', get_called_class()),
                E_USER_ERROR
            );
        }

        $deleted = $this->db->driver($this->driver)->delete(
            $this->table, "WHERE {$this->where}", $this->bindings
        );

        $this->clear();

        return $deleted;
    }
}
