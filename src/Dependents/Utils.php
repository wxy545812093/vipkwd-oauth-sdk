<?php

declare(strict_types=1);

namespace Vipkwd\OAuth\Dependents;

class Utils{
    /**
     * 检测数组是否为索引数组(且从0序开始)
     *
     *
     * @param mixed $value
     */
    static function isIndexList($value): bool
    {
        return is_array($value) && (PHP_VERSION_ID < 80100
            ? !$value || array_keys($value) === range(0, count($value) - 1)
            : array_is_list($value)
        );
    }
    /**
     * 字符XSS过滤
     *
     * @param string|array $str 待检字符 或 索引数组
     * @param boolean $dpi <false> 除常规过滤外，是否深度(额外使用正则)过滤。默认false仅常规过滤
     * @return string|array
     */
    static function removeXss($str, bool $dpi = false, bool $keepHtmlTag = false)
    {
        if (!is_array($str)) {
            $str = trim($str);
            $keepHtmlTag === false && $str = strip_tags($str);
            $str = htmlspecialchars($str);
            if ($dpi === true) {
                $str = str_replace(array('"', "\\", "'", "/", "..", "../", "./", "//"), '', $str);
                $no = '/%0[0-8bcef]/';
                $str = preg_replace($no, '', $str);

                // 移除 百分号后两位字符
                $no = '/%1[0-9a-f]/';
                $str = preg_replace($no, '', $str);

                // 移除W3C的标准下，XML文件无法识别的字符
                $no = '/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]+/S';
                $str = preg_replace($no, '', $str);
            }
            return $str;
        }
        foreach ($str as $k => $v) {
            $str[$k] = self::removeXss($v, $dpi);
        }
        return $str;
    }

    /**
     * 数据解码(xss)
     * 
     * @param mixed $data
     * @param bool $rxss <true> 是否转义为实体符
     * 
     * @return mixed
     * 
     */
    static function encode($data, $rxss = true)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = self::encode($v, $rxss);
            }
            return $data;
        } else {
            $type = strtolower(gettype($data));
            switch ($type) {
                case "boolean":
                case "object":
                case "integer":
                case "double":
                case "null":
                    break;
                case "string":
                    $data = trim($data);
                    $firstChar = substr($data, 0, 1);
                    $lastChar = substr($data, -1);
                    if (($firstChar == '{' &&  $lastChar == '}') || ($firstChar == '[' &&  $lastChar == ']')) {
                        return self::encode(json_decode($data, true), $rxss);
                    } else {
                        $data = urldecode($data);
                        $data = trim($rxss ? self::removeXss($data) : $data);
                    }
                    break;
                default:
                    $data = Null;
                    break;
            }
            return $data;
        }
    }

}