<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\parser;

use bashkarev\email\Parser;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Address
{
    /**
     * @param $text
     * @param $charset
     * @return array
     */
    public function parse($text, $charset)
    {
        $addresses = [];
        $values = explode(',', $text);
        foreach ($values as $value) {
            $address = $this->item($value);
            if ($address->email === null) {
                continue;
            }
            if ($address->name !== null && $charset !== Parser::$charset) {
                $address->name = mb_convert_encoding($address->name, Parser::$charset, $charset);
            }
            $addresses[] = $address;

        }
        return $addresses;
    }

    /**
     * @param $string
     * @return \bashkarev\email\Address
     */
    private function item($string)
    {
        $address = new \bashkarev\email\Address();
        foreach (explode(' ', $string) as $value) {
            $value = ltrim($value, " \t\"<'(");
            $value = rtrim($value, " \t\">')");
            if ($address->email === null && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $address->email = $value;
            } else {
                if ($address->name !== null) {
                    $address->name .= ' ';
                }
                $address->name .= $value;
            }
        }
        return $address;
    }
}