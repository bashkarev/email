<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Parser;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class TestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @param mixed $value
     * @param mixed $expected
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
        $actual = is_callable($filed) ? $filed($message) : $message->getHeaderLine($filed);
        $this->assertEquals($expected, $actual, $description);
        return $this;
    }

    /**
     * @param mixed $file
     * @return \bashkarev\email\Message
     */
    protected function message($file)
    {
        if (is_string($file)) {
            if ($file[0] === '/') {
                $stream = fopen(__DIR__ . "/fixtures{$file}", 'rb+');
            } else {
                $stream = fopen('php://memory', 'rb+');
                fwrite($stream, $file);
            }
        } else {
            $stream = $file;
        }
        return Parser::email($stream);
    }

    /**
     * Returns property value
     *
     * @param string $className
     * @param string $propertyName
     * @return \ReflectionProperty
     */
    protected function getProperty($className, $propertyName)
    {
        $reflector = new \ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

}