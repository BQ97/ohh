<?php

declare(strict_types=1);

namespace App;

class MyTree
{
    /**
     * @var string
     */
    private string $parentId = 'pid';

    /**
     * @var string
     */
    private string $id = 'id';

    /**
     * @var string
     */
    private string $name = 'name';

    /**
     * @var array
     */
    private string $child = 'child';

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->id = $config['id'] ?? 'id';
        $this->name = $config['name'] ?? 'name';
        $this->parentId = $config['pid'] ?? 'pid';
        $this->child = $config['child'] ?? 'child';
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
        if (!$data) {
            return '';
        }
        $data = $this->formatArray($data);

        foreach ($data as $item) {
            $data[$item[$this->parentId]][$this->child][] = &$data[$item[$this->id]];
        }
        $result = $data[$parentId];
        return $result;
    }

    public function getListByTree(array $tree, int|string $parentId = ''): array
    {
        return array_reduce($tree, function ($carry, $item) use ($parentId) {
            if ($item[$this->parentId] == $parentId) {

                $children = $item[$this->child] ?? [];

                unset($item[$this->child]);

                $carry[] = $item;

                if ($children) {

                    $carry = array_merge($carry, $this->getListByTree($children, $item[$this->id]));
                }
            }

            return $carry;
        }, []);
    }

    public function getChild(array $data, int|string $id, bool $include = false): string
    {
        if (!$data) {
            return '';
        }

        $tempArray = $this->getTree($data, $id);
        $w = '';
        if ($tempArray) {
            if ($include && $id != 0) {
                $w .= $tempArray[$this->id] . ',';
            }

            if (isset($tempArray[$this->child])) {
                foreach ($tempArray[$this->child] as $e) {
                    if (isset($e[$this->child])) {
                        $w .= $e[$this->id] . ',' . $this->getChild($data, $e[$this->id]) . ',';
                    } else {
                        $w .= $e[$this->id] . ',';
                    }
                }
            }
        }
        $r = rtrim($w, ',');
        return $r;
    }

    public function getParent(array $data, int|string $id): array
    {
        if (!$data) {
            return [];
        }
        $data = $this->formatArray($data);
        $parentId = $data[$id][$this->parentId];
        if ($parentId) {
            return $data[$parentId];
        } else {
            return $data[$id];
        }
    }

    public function getBreadCrumb(array $data, int|string $id): array
    {
        if (!$data) {
            return [];
        }

        $data = $this->formatArray($data);
        $breadcrumb = [];
        while ($id != 0) {
            $breadcrumb[] = $data[$id];
            $id = $data[$id][$this->parentId];
        }

        return $breadcrumb;
    }

    private function formatArray(array $data): array
    {
        return array_column($data, null, $this->id);
    }
}
