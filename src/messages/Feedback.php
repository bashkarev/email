<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\messages;

use bashkarev\email\helpers\Address;
use bashkarev\email\Message;

/**
 *
 * @see https://tools.ietf.org/html/rfc5965
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Feedback extends Message
{
    /**
     * Spam or some other kind of email abuse;
     */
    const TYPE_ABUSE = 'abuse';
    /**
     * Indicates some kind of fraud or phishing activity;
     */
    const TYPE_FRAUD = 'fraud';
    /**
     * Report of a virus found in the originating message;
     */
    const TYPE_VIRUS = 'virus';
    /**
     * Any other feedback that doesn't fit into other types;
     */
    const TYPE_OTHER = 'other';
    /**
     * Can be used to report an email message that was mistakenly marked as spam
     */
    const TYPE_NOT_SPAM = 'not-spam';

    /**
     * @return null|string
     */
    public function getType()
    {
        if (!$this->hasHeader('feedback-type')) {
            return null;
        }
        return $this->getHeaderLine('feedback-type');
    }

    /**
     * @return null|string
     */
    public function getUserAgent()
    {
        if (!$this->hasHeader('user-agent')) {
            return null;
        }
        return $this->getHeaderLine('user-agent');
    }

    /**
     * @return null|string
     */
    public function getVersion()
    {
        if (!$this->hasHeader('version')) {
            return null;
        }
        return $this->getHeaderLine('version');
    }

    /**
     * @return null|string
     */
    public function getOriginalEnvelopeId()
    {
        if (!$this->hasHeader('original-envelope-id')) {
            return null;
        }
        return $this->getHeaderLine('Original-Envelope-Id');
    }

    /**
     * @return \bashkarev\email\Address|null
     */
    public function getOriginalMailFrom()
    {
        $from = Address::parse($this->getHeaderLine('Original-Mail-From'), $this->getCharset());
        if ($from === []) {
            return null;
        }
        return $from[0];
    }

    /**
     * @return \DateTime|null
     */
    public function getArrivalDate()
    {
        if (!$this->hasHeader('arrival-date')) {
            return null;
        }

        try {
            $date = new \DateTime($this->getHeaderLine('arrival-date'));
        } catch (\Exception $e) {
            return null;
        }
        return $date;
    }

    /**
     * @return array
     */
    public function getReportingMTA()
    {
        return $this->getHeader('reporting-mta');
    }

    /**
     * @return null|string
     */
    public function getSourceIP()
    {
        if (!$this->hasHeader('source-ip')) {
            return null;
        }
        return $this->getHeaderLine('source-ip');
    }

    /**
     * @return int|null
     */
    public function getIncidents()
    {
        if (!$this->hasHeader('incidents')) {
            return null;
        }
        return (int)$this->getHeader('incidents');
    }

}