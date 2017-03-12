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

        $eml = <<<EOF
Content-Type: multipart/mixed;boundary=test

--test
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

http://test=
-local.ru
EOF;
        $this->field($eml, function (Message $message) {
            $this->assertEquals("http://test-local.ru", $message->textPlain());
        });

        $eml = <<<EOF
Content-Type: multipart/mixed;boundary=test

--test
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

test
-
EOF;
        $this->field($eml, function (Message $message) {
            $this->assertContains("-", $message->textPlain());
        });

        $eml = <<<EOF
Content-Type: multipart/mixed;boundary=test

--test
Content-Type: text/plain; charset=utf-8
Content-Transfer-Encoding: quoted-printable

-
test
EOF;
        $this->field($eml, function (Message $message) {
            $this->assertContains("-", $message->textPlain());
        });

    }

}