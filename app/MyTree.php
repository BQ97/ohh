<?php

declare(strict_types=1);

namespace App;

class MyTree
{
    /**
     * @var string
     */
    private string $_parentId = 'pid';

    /**
     * @var string
     */
    private string $_id = 'id';

    /**
     * @var string
     */
    private string $_name = 'name';

    /**
     * @var array
     */
    private string $_child = 'child';

    /**
     * @param array config
     */
    public function __construct(array $config = [])
    {
        $this->_parentId = $config['parentId'] ?? 'pid';
        $this->_id = $config['id'] ?? 'id';
        $this->_name = $config['name'] ?? 'name';
        $this->_child = $config['child'] ?? 'child';
    }

    /**
     * @param array config
     * @return MyTree
     */
    public static function getInstance(array $config = [])
    {
        return new static($config);
    }

    public function getTree(array $data, int|string $parentId = 0)
    {
        if (!$data || !is_array($data)) {
            return '';
        }
        $data = $this->formatArray($data);

        foreach ($data as $item) {
            $data[$item[$this->_parentId]][$this->_child][] = &$data[$item[$this->_id]];
        }
        $result = $data[$parentId];
        return $result;
    }

    public function getListByTree(array $tree, int|string $parentId = '')
    {
        return array_reduce($tree, function ($carry, $item) use ($parentId) {
            if ($item[$this->_parentId] == $parentId) {

                $children = $item[$this->_child] ?? [];

                unset($item[$this->_child]);

                $carry[] = $item;

                if ($children) {

                    $carry = array_merge($carry, $this->getListByTree($children, $item[$this->_id]));
                }
            }

            return $carry;
        }, []);
    }

    public function getChild(array $data, int|string $id, bool $include = false)
    {
        if (!$data || !is_array($data)) {
            return array();
        }

        $tempArray = $this->getTree($data, $id);
        $w = '';
        if ($tempArray) {
            if ($include && $id != 0) {
                $w .= $tempArray[$this->_id] . ',';
            }

            if (isset($tempArray[$this->_child])) {
                foreach ($tempArray[$this->_child] as $e) {
                    if (isset($e[$this->_child])) {
                        $w .= $e[$this->_id] . ',' . $this->getChild($data, $e[$this->_id]) . ',';
                    } else {
                        $w .= $e[$this->_id] . ',';
                    }
                }
            }
        }
        $r = rtrim($w, ',');
        return $r;
    }

    public function getParent(array $data, int|string $id)
    {
        if (!$data || !is_array($data)) {
            return array();
        }
        $data = $this->formatArray($data);
        $parentId = $data[$id][$this->_parentId];
        if ($parentId) {
            return $data[$parentId];
        } else {
            return $data[$id];
        }
    }

    public function getBreadCrumb(array $data, int|string $id)
    {
        if (!$data || !is_array($data)) {
            return [];
        }

        $data = $this->formatArray($data);
        $breadcrumb = [];
        while ($id != 0) {
            $breadcrumb[] = $data[$id];
            $id = $data[$id][$this->_parentId];
        }

        return $breadcrumb;
    }

    private function formatArray(array $data)
    {
        $index = [];
        foreach ($data as $value) {
            $index[$value[$this->_id]] = $value;
        }
        return $index;
    }
}
