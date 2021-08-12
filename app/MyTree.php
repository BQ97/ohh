<?php
declare(strict_types=1);

namespace App;

class MyTree
{
    private $_parentId;
    private $_id;
    private $_name;
    private $_child;

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

    public function getHtmlOption($data, $id = 0, $parentId=-1, $selectId = null, $preFix = '|-')
    {
        if (!$data || !is_array($data)) {
            return '';
        }

        $string = '';
        foreach ($data as $key => $value) {
            if ($value[$this->_id] == $id) {
                $string .= '<option value=\'' . $value[$this->_id] . '\'';
                if (!is_null($selectId)) {
                    $string .= ($value[$this->_id] == $selectId) ? ' selected="selected"' : '';
                }
                $string .= '>' . $preFix . $value[$this->_name] . '</option>';
                $parentId=$id;
            }
            
            if ($value[$this->_parentId] == $parentId) {
                $string .= '<option value=\'' . $value[$this->_id] . '\'';
                if (!is_null($selectId)) {
                    $string .= ($value[$this->_id] == $selectId) ? ' selected="selected"' : '';
                }
                $string .= '>' . '&nbsp;&nbsp;' . $preFix . $value[$this->_name] . '</option>';
                $string .= $this->getHtmlOption($data, -1, $value[$this->_id], $selectId, '&nbsp;&nbsp;' . $preFix);
            }
        }
        return $string;
    }


    public function getTree($data, $parentId = 0)
    {
        if (!$data || !is_array($data)) {
            return '';
        }
        $data=$this->formatArray($data);

        foreach ($data as $item) {
            $data[$item[$this->_parentId]][$this->_child][] = &$data[$item[$this->_id]];
        }
        $result=$data[$parentId];
        return $result;
    }

    public function getTreeHtml($data, $url="", $parentId = 0)
    {
        if (!$data || !is_array($data)) {
            return '';
        }

        $string="";
        foreach ($data as $key => $value) {
            if ($value[$this->_parentId] == $parentId) {
                $string .= '<li>' . '<a href="' .$url.'/cid/'. $value[$this->_id] . '">' . $value[$this->_name] . '</a>';
                $string .= $this->getTreeHtml($data, $url, $value[$this->_id]).'</li>';
            }
        }
        if (!empty($string)&&$parentId!=0) {
            $string = '<ul>'.$string.'</ul>';
        }
        return $string;
    }

    public function getChild($data, $id, $include=false)
    {
        if (!$data || !is_array($data)) {
            return array();
        }
        // $data=$this->formatArray($data);

        $tempArray = $this->getTree($data, $id);
        $w='';
        if ($tempArray) {
            if ($include&&$id!=0) {
                $w.=$tempArray[$this->_id].',';
            }

            if (isset($tempArray[$this->_child])) {
                foreach ($tempArray[$this->_child] as $e) {
                    if (isset($e[$this->_child])) {
                        $w .= $e[$this->_id].','. $this->getChild($data, $e[$this->_id]).',';
                    } else {
                        $w.=$e[$this->_id].',';
                    }
                }
            }
        }
        $r=rtrim($w, ',');
        return $r;
    }

    public function getParent($data, $id)
    {
        if (!$data || !is_array($data)) {
            return array();
        }
        $data=$this->formatArray($data);
        $parentId = $data[$id][$this->_parentId];
        if ($parentId) {
            return $data[$parentId];
        } else {
            return $data[$id];
        }
    }

    public function breadcrumbNavigation($data, $id)
    {
        if (!$data || !is_array($data)) {
            return array();
        }

        $data=$this->formatArray($data);
        $breadcrumb=array();
        while ($id!=0) {
            $breadcrumb[]=$data[$id];
            $id=$data[$id][$this->_parentId];
        }

        return $breadcrumb;
    }

    private function formatArray($data)
    {
        $index=array();
        foreach ($data as $value) {
            $index[$value[$this->_id]]=$value;
        }
        return $index;
    }
}
