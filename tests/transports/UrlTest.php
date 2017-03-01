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
class UrlTest extends TestCase
{

    public function testParsing()
    {
        $this->field('/external-url.eml', function (Message $message) {
            /**
             * @var \bashkarev\email\transports\Url $transport
             */
            $transport = $message->getStream();
            $this->assertInstanceOf('bashkarev\email\transports\Url', $transport);
            $this->assertContains('<!DOCTYPE html>', $transport->getContents());
            $this->assertContains('<!DOCTYPE html>', $message->textHtml());
            $this->assertTrue($transport->hasHeaders());
            $this->assertEquals('text/html', $transport->getMimeType());

        });
    }

}