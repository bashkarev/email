<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\helpers;

use bashkarev\email\Parser;

/**
 *
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class RFC5987
{

    /**
     * @param array $headers
     * @return null|string
     */
    public static function filename($headers)
    {
        if ($headers === []) {
            return null;
        }
        $name = null;
        $charset = null;
        $encode = null;
        foreach ($headers as $head) {
            if (strncasecmp($head, 'filename', 8) !== 0) {
                continue;
            }
            if ($head[8] === '*') {
                if (preg_match('/filename\*(0\*|)=([^\']+)\'\'(.*)/', $head, $out)) {
                    $charset = mb_strtoupper($out[2]);
                    $encode = $out[3];
                } else {
                    $encode .= preg_replace('/filename\*\d+\*\=/', '', $head);
                }
            } else {
                $name = str_replace(['filename', '"', ' ', '='], '', $head);
            }
        }

        if ($encode !== null) {
            $name = urldecode($encode);
            if ($charset !== Parser::$charset) {
                $name = mb_convert_encoding($name, Parser::$charset, $charset);
            }
        }
        return $name;
    }

}