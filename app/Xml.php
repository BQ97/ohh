<?php

declare(strict_types=1);

namespace App;

class Xml
{
    public static function array2Xml(array $arr)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } elseif (is_array($val)) {
                $xml .= "<" . $key . ">" . str_replace(['<xml>', '</xml>', '<?xml version="1.0" encoding="UTF-8"?>'], '', static::array2Xml($val)) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" .  $val  . "</" . $key . ">";
            }
        }
        return $xml;
    }

    public static function xml2Array(string $xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
}
