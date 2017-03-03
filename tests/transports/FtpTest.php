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
class FtpTest extends TestCase
{

    public function testParsing()
    {
        $this->field('/external-ftp.eml', function (Message $message) {
            $attachment = $message->getAttachments()[0];
            $this->assertEquals('message/external-body', $attachment->getMimeType());
            $this->assertInstanceOf('bashkarev\email\transports\Ftp', $attachment->getStream());
            $this->assertEquals('application/zip', $attachment->getStream()->getMimeType());
            $this->assertStringEqualsFile(__DIR__ . '/../fixtures/attachments/1KB.zip', $attachment->getStream()->getContents());
        });
    }

}