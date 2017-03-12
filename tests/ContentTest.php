<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Message;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class ContentTest extends TestCase
{

    public function testEOL()
    {
        // \r\n - WIN
        // \n   - other
        $eml = "Content-Type: multipart/mixed;\n\tboundary=test\n--test\nContent-Type: text/plain;\n\ntext";
        $this->field($eml, function (Message $message) {
            $this->assertEquals('text', $message->textPlain());
        });

        $eml = "Content-Type: multipart/mixed;\n\tboundary=test\n--test\nContent-Type: text/plain;\n\ntext\n--not-boundary";
        $this->field($eml, function (Message $message) {
            $this->assertEquals("text\n--not-boundary", $message->textPlain());
        });

        $eml = "Content-Type: multipart/mixed;\r\n\tboundary=test\r\n--test\r\nContent-Type: text/plain;\r\n\r\ntext";
        $this->field($eml, function (Message $message) {
            $this->assertEquals('text', $message->textPlain());
        });

        $eml = "Content-Type: multipart/mixed;\r\n\tboundary=test\r\n--test\r\nContent-Type: text/plain;\r\n\r\ntext\r\n--not-boundary";
        $this->field($eml, function (Message $message) {
            $this->assertEquals("text\r\n--not-boundary", $message->textPlain());
        });

    }

    public function testSeparator()
    {
        $this
            ->field('/parser/content-separator-1.eml', function (Message $message) {
                $this->assertEquals("http://test-local.ru", $message->textPlain());
            })
            ->field('/parser/content-separator-2.eml', function (Message $message) {
                $this->assertContains("-", $message->textPlain());
            })
            ->field('/parser/content-separator-3.eml', function (Message $message) {
                $this->assertContains("-", $message->textPlain());
            });
    }

}