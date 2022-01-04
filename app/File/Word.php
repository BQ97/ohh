<?php

declare(strict_types=1);

namespace App\File;

use PhpOffice\PhpWord\{Reader\Word2007, Element\Text, Element\Table, Element\TextRun};
use Exception;

/**
 * PHPWord
 */
class Word
{
    private function getTextByTextRun(TextRun $elem)
    {
        $data = [];

        foreach ($elem->getElements() as $k => $item) {
            if ($item instanceof Text) {

                $style = $item->getFontStyle();

                $family = $style->getName();

                $size = $style->getSize();

                $text = $item->getText();

                $array = [
                    'type' => 'text',
                    'text' => $text
                ];

                if ($family == '苹方-简' && $size == 24) {
                    $array['type'] = 'real_name';
                    $array['text'] = $text;
                } elseif ($family == 'PINGFANG SC SEMIBOLD' && $size == 12) {
                    $array['type'] = 'title';
                    $array['text'] = $text;
                } else {

                    $i = strpos($text, '邮箱：');
                    if ($i !== false) {
                        $array['text'] = substr($text, strlen('邮箱：') + $i);
                        $array['type'] = 'email';
                    }
                    $i = strpos($text, '电话：');
                    if ($i !== false) {
                        $array['text'] = substr($text, strlen('电话：') + $i, 11);
                        $array['type'] = 'mobile';
                    }
                    $i = strpos($text, '手机：');
                    if ($i !== false) {
                        $array['text'] = substr($text, strlen('手机：') + $i, 11);
                        $array['type'] = 'mobile';
                    }

                    $i = strpos($text, '应聘职位：');
                    if ($i !== false) {
                        $array['text'] = trim(substr($text, strlen('应聘职位：') + $i, strpos($text, '应聘企业：') - strlen('应聘企业：')));
                        $array['type'] = 'position';
                    }

                    if (in_array($text, ['男', '女'])) {
                        $array['text'] = $text;
                        $array['type'] = 'sex';
                    }
                }

                $data[] = $array;
            }
        }

        return $data;
    }

    /**
     * 读取 word 文件
     * @param boolean   fileName 	文件名
     *
     * @return array
     */
    public function read($fileName)
    {
        try {
            if (!file_exists($fileName)) {
                throw new Exception('文件不存在');
            }
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        $word = new Word2007;

        $phpword = $word->load($fileName);

        $sections = $phpword->getSections();

        $data = [];

        foreach ($sections as $section) {

            $elems = $section->getElements();

            foreach ($elems as $elem) {

                $text = [];

                if ($elem instanceof TextRun) {

                    $text = array_merge($text, $this->getTextByTextRun($elem));
                } elseif ($elem instanceof Table) {
                    foreach ($elem->getRows() as $row) {
                        foreach ($row->getCells() as $cell) {
                            foreach ($cell->getElements() as $ce) {
                                if ($ce instanceof TextRun) {
                                    $text = array_merge($text, $this->getTextByTextRun($ce));
                                }
                            }
                        }
                    }
                }

                if ($text) {
                    foreach ($text as  $t) {

                        if ($t['type'] == 'title') {

                            $data[] = [
                                'type' => 'item',
                                'title' => $t['text'],
                                'item' => []
                            ];
                        } else {
                            $idx = count($data) - 1;
                            if (isset($data[$idx]['item'])) {
                                $data[$idx]['item'][] = $t['text'];
                            } else {
                                $data[] = $t;
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}
