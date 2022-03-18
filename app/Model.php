<?php

declare(strict_types=1);

namespace App;

use Medoo\{Medoo, Raw};
use PDO;

/**
 * Class Model
 * @package boqing
 * @method array select(string $table, array|string $join = null, array|string $columns = null, array $where = null) 查询
 * @method array get(string $table, array|string $join = null, array|string $columns = null, array $where = null) 单条查询
 * @method \Medoo\Raw raw(string $string, array $map = [])
 * @method \PDOStatement query(string $query, array $map = [])
 * @method \PDOStatement insert(string $table, array $datas) 新增数据
 * @method \PDOStatement update(string $table, array $data, array $where) 修改数据
 * @method \PDOStatement delete(string $table, array $where) 删除数据
 * @method int id() 上次插入ID
 */
class Model
{
    private string $primarykey = 'id';

    private string $table;

    private array $encodeFields = [];

    private array $decodeFields = [];

    /**
     * @var Medoo
     */
    private Medoo $medoo;

    public function __construct(Medoo $medoo)
    {
        $this->medoo = $medoo;
    }

    /**
     * 设置表名
     * @param string $table 表名
     * @return Model
     */
    public function setTable(String $table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置主键
     * @param string $pk  主键
     * @return Model
     */
    public function setPk(String $pk)
    {
        $this->primarykey = $pk;
        return $this;
    }

    /**
     * @example
     * [
     *      'contact' => 'contact[JSON]',
     *      'order_goods' => 'order_goods[JSON]'
     * ]
     *
     * @return \App\Model
     */
    public function fieldEncode(array $fields)
    {
        $this->encodeFields = $fields;

        return $this;
    }

    /**
     * @example
     * [
     *      'contact' => 'contact[JSON]',
     *      'order_goods' => 'order_goods[JSON]',
     *      'create_time' => $this->raw('FROM_UNIXTIME(<create_time>)')
     * ]
     *
     * @return \App\Model
     */
    public function fieldDecode(array $fields)
    {
        $this->decodeFields = $fields;

        return $this;
    }

    /**
     * 格式化查询字段
     * @return array
     */
    private function formatQueryFields()
    {
        $fields = array_column($this->getAllFields(), 'field');

        $decodeFields = $this->decodeFields;

        if ($decodeFields) {
            foreach ($fields as $key => $field) {
                if (isset($decodeFields[$field])) {
                    if ($decodeFields[$field] instanceof Raw) {
                        $fields[$field] = $decodeFields[$field];
                    } else {
                        $fields[$key] = $decodeFields[$field];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * 单条查询
     * @param array $where
     * @return array
     */
    public function fetch(array $where = [])
    {
        $fields = $this->formatQueryFields();

        return $this->get($this->table, $fields, $where);
    }

    /**
     * 多条查询
     * @param array $where
     * @param int $page
     * @param int $pageNum
     *
     * @return array
     */
    public function fetchList(array $where = [], int $page = 1, int $pageNum = 10)
    {
        $total = $this->count($this->table, $this->primarykey, $where);
        if ($total == 0) {
            return ['total' => 0, 'data' => []];
        }

        $where['LIMIT'] = [($page - 1) * $pageNum, $pageNum];
        $where['ORDER'] = [$this->primarykey => 'DESC'];

        $fields = $this->formatQueryFields();

        $data = $this->select($this->table, $fields, $where);

        return ['total' => $total, 'data' => $data];
    }

    /**
     * 获取数据库表所有字段
     *
     * @return array
     */
    private function getAllFields()
    {
        $table = $this->tableQuote($this->table);

        $fields = $this->query("SHOW COLUMNS FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($fields as $key => $value) {
            $value = array_change_key_case($value);
            $value['type'] = explode('(', $value['type'])[0];

            $data[$value['field']] = $value;
        }

        return $data;
    }

    /**
     * 过滤字段
     * @param array $param
     * @return array
     */
    private function filter(array $param)
    {
        $fields = $this->getAllFields();

        $data = [];

        $encodeFields = $this->encodeFields;

        foreach ($fields as $key => $value) {
            if ($value['key'] === 'PRI') {
                continue;
            }

            if ($value['extra'] === 'auto_increment') {
                continue;
            }

            if (empty($param[$value['field']])) {
                $data[$value['field']] = $value['default'];
            } else {
                if (isset($encodeFields[$value['field']])) {
                    $data[$encodeFields[$value['field']]] = $param[$value['field']];
                } else {
                    $data[$value['field']] = $param[$value['field']];
                }
            }
        }

        return $data;
    }

    /**
     * 更新数据，有主键更新，没主键新增
     * @param array $data
     * @return int  影响行数
     */
    public function save(array $data)
    {
        $saveData = $this->filter($data);

        if (empty($data[$this->primarykey])) {

            $pdosmt = $this->insert($this->table, $saveData);

            return $pdosmt->rowCount();
        }

        $pdosmt = $this->update($this->table, $this->filter($data), [
            $this->primarykey => $data[$this->primarykey]
        ]);

        return $pdosmt->rowCount();
    }

    /**
     * 调用\Medoo\Medoo下所有的方法
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->medoo, $method], $args);
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);
        unset($data['medoo']);
        return $data;
    }
}
