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
            $this->assertInstanceOf('bashkarev\email\transports\Url', $message->getStream());
            $this->assertContains('<!DOCTYPE html>', $message->getStream()->getContents());
        });
    }

}