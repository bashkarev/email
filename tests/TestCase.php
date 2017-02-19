<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Parser;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @param $value
     * @param $expected
     * @param string|callable $filed
     * @param string $description
     * @return $this
     */
    protected function field($value, $expected, $filed = null, $description = '')
    {
        $message = $this->message($value);
        if (is_callable($expected)) {
            $expected($message);
            return $this;
        }
        $actual = (is_callable($filed)) ? $filed($message) : $message->getHeaderLine($filed);
        $this->assertEquals($expected, $actual, $description);
        return $this;
    }

    /**
     * @param $file
     * @return \bashkarev\email\Message
     */
    protected function message($file)
    {
        if (is_string($file)) {
            if ($file[0] === '/') {
                $steam = fopen(__DIR__ . "/fixtures{$file}", 'r+');
            } else {
                $steam = fopen('php://memory', 'r+');
                fwrite($steam, $file);
            }
        } else {
            $steam = $file;
        }
        return Parser::email($steam);
    }

    /**
     * @param $file
     * @return string
     */
    protected function html($file)
    {
        return file_get_contents(__DIR__ . "/fixtures{$file}");
    }

}