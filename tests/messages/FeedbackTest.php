<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests\messages;

use bashkarev\email\Message;
use bashkarev\email\messages\Feedback;
use bashkarev\email\tests\TestCase;

/**
 *
 * @see https://tools.ietf.org/html/rfc5965
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class FeedbackTest extends TestCase
{

    public function testComplaints()
    {
        $this
            ->field('/complaints/aol.eml', function (Message $container) {
                /**
                 * @var Feedback $feedback
                 */
                $feedback = null;
                foreach ($container->getAttachments() as $attachment) {
                    if ($attachment->getMimeType() === 'message/feedback-report') {
                        $feedback = $attachment->getMessage();
                    }
                }

                $this->assertEquals(Feedback::TYPE_ABUSE, $feedback->getType());
                $this->assertEquals('AOL SComp', $feedback->getUserAgent());
                $this->assertEquals('0.1', $feedback->getVersion());
                $this->assertNull($feedback->getOriginalEnvelopeId());
            })
            ->field('/complaints/yahoo.eml', function (Message $container) {
                /**
                 * @var Feedback $feedback
                 */
                $feedback = null;
                foreach ($container->getAttachments() as $attachment) {
                    if ($attachment->getMimeType() === 'message/feedback-report') {
                        $feedback = $attachment->getMessage();
                    }
                }
                $this->assertEquals(Feedback::TYPE_ABUSE, $feedback->getType());
                $this->assertEquals('0.1', $feedback->getVersion());
                $this->assertEquals('Yahoo!-Mail-Feedback/1.0', $feedback->getUserAgent());
                $this->assertEquals('bounce+705f77.d4a-user=yahoo.com@example.com', $feedback->getOriginalMailFrom()->email);
            });
    }

}