<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Message;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class BoundaryTest extends TestCase
{

    /**
     * toDo
     * * right spaces
     * * uppercase
     */
    public function testName()
    {

    }

    public function testEOL()
    {
        // \r\n - WIN
        // \n   - other
        $eml = "Content-Type: multipart/mixed;\n\tboundary=test\n--test\nContent-Type: text/plain;\n\ntext";
        $this->field($eml, function (Message $message) {
            $this->assertEquals('text', $message->textPlain());
        });

        $eml = "Content-Type: multipart/mixed;\r\n\tboundary=test\r\n--test\r\nContent-Type: text/plain;\r\n\r\ntext";
        $this->field($eml, function (Message $message) {
            $this->assertEquals('text', $message->textPlain());
        });
    }
}