<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email;

use bashkarev\email\parser\Address;
use bashkarev\email\parser\Email;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Parser
{
    public static $buffer = 500000;
    public static $charset = 'UTF-8';

    /**
     * @param $handle
     * @return Message
     */
    public static function email($handle)
    {
        $parser = new Email();
        return $parser->parse($handle);
    }

    /**
     * @param $value
     * @param $charset
     * @return \bashkarev\email\Address[]
     */
    public static function address($value, $charset)
    {
        $parser = new Address();
        return $parser->parse($value, $charset);
    }

}