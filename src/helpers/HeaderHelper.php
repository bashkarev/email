<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\helpers;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class HeaderHelper
{

    /**
     * parsing string from Content-type: text/html to ['Content-type','text/html']
     * @param string $line
     * @return array
     */
    public static function parse($line)
    {
        $data = explode(':', $line);
        $filed = $data[0];
        if (isset($data[2])) {
            unset($data[0]);
            $value = implode(':', $data);
        } else {
            $value = $data[1];
        }
        return [rtrim($filed), ltrim($value)];
    }

}