<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests;

use DateTime;
use bashkarev\email\Message;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class MessageTest extends TestCase
{

    public function testDate()
    {
        $this
            ->field('Date:     26 Aug 76 1429 EDT','Thu, 26 Aug 1976 14:29:00 -0400', function (Message $message) {
                return $message->getDate()->format(DateTime::RFC2822);
            },'valid rfc822')
            ->field('Date: Thu, 2 Feb 2017 10:46:57 +0100 (CET)', 'Thu, 02 Feb 2017 10:46:57 +0100', function (Message $message) {
                return $message->getDate()->format(DateTime::RFC2822);
            },'valid rfc2822')
            ->field('Date: This is not a date', function (Message $message) {
                $this->assertNull($message->getDate());
            }, 'invalid')
            ->field('Date:  ', function (Message $message) {
                $this->assertNull($message->getDate());
            }, 'empty')
            ->field('Date: Sun, 16 Jun 2013 17:50:12 +0200', 'Sun, 16 Jun 2013 17:50:12 +0200', function (Message $message) {
                return $message->getDate()->format(DateTime::RFC2822);
            });
    }

    public function testHtml()
    {
        $this
            ->field('/nested.eml', function (Message $message) {
                $this->assertStringEqualsFile(__DIR__.'/fixtures/text/nested.txt',$message->textHtml());
            })
            ->field('/non-unique-cid.eml', function (Message $message) {
                $this->assertContains(
                    'data:image/gif;base64,/9j/4AAQSkZJRgABAQEASABIAAD//gAeaHR0cDovL3RpbGVkLWJnLmJsb2dzcG90LmNvbf',
                    $message->textHtml(true)
                );
            });

    }
}