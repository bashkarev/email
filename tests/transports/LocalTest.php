<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests\transports;

use bashkarev\email\Message;
use bashkarev\email\tests\TestCase;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class LocalTest extends TestCase
{

    public function testParsing()
    {
        $path = __DIR__ . '/../fixtures/attachments/hello.html';
        $this->field($this->generate($path), function (Message $message) use ($path) {
            /**
             * @var \bashkarev\email\transports\Local $transport
             */
            $transport = $message->getStream();
            $this->assertInstanceOf('bashkarev\email\transports\Local', $transport);
            $this->assertStringEqualsFile($path, $message->textHtml());
        });
    }

    protected function generate($path)
    {
        $file = realpath($path);
        return <<<EOF
MIME-Version: 1.0
Subject: hello
Content-type: message/external-body; access-type=local-file;
              name="$file"

Content-type: text/html
Content-Transfer-Encoding: binary

THIS IS NOT REALLY THE BODY!";
EOF;
    }

}