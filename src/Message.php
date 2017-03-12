<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email;

use bashkarev\email\helpers\Address;
use DateTime;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Message extends Mime
{
    /**
     * @var Part[]
     */
    protected $attachments = [];
    /**
     * @var Part[]
     */
    protected $parts = [];

    /**
     * @return DateTime|null
     */
    public function getDate()
    {
        $value = $this->getHeaderLine('date');
        if ($value === '') {
            return null;
        }
        try {
            $date = new DateTime($value);
        } catch (\Exception $e) {
            return null;
        }
        return $date;
    }

    /**
     * @return \bashkarev\email\Address|null
     */
    public function getFrom()
    {
        $from = Address::parse($this->getHeaderLine('from'), $this->getCharset());
        if ($from === []) {
            return null;
        }
        return $from[0];
    }

    /**
     * @return \bashkarev\email\Address|null
     */
    public function getSender()
    {
        $from = Address::parse($this->getHeaderLine('sender'), $this->getCharset());
        if ($from === []) {
            return null;
        }
        return $from[0];
    }

    /**
     * @return \bashkarev\email\Address[]
     */
    public function getTo()
    {
        return Address::parse($this->getHeaderLine('to'), $this->getCharset());
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        if ($this->hasHeader('subject') === false) {
            return null;
        }
        $str = $this->getHeaderLine('subject');
        $charset = $this->getCharset();
        if ($charset !== Parser::$charset) {
            $str = mb_convert_encoding($str, Parser::$charset, $charset);
        }
        return $str;
    }

    /**
     * @param bool $cid
     * @return string
     */
    public function textHtml($cid = true)
    {
        $parts = $this->findTextParts('text/html');
        if ($parts === []) {
            $parts = $this->findTextParts('text/plain');
        }

        if ($parts === []) {
            return '';
        }

        $html = '';
        foreach ($parts as $part) {
            $data = $part->getStream()->getContents();
            if ($part->getMimeType() === 'text/plain') {
                $data = nl2br($data);
            }
            $html .= $data;
        }

        if ($cid === true) {
            $html = $this->replaceCid($html);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function textPlain()
    {
        $text = '';
        foreach ($this->findTextParts('text/plain') as $part) {
            $text .= $part->getStream()->getContents();
        }
        return $text;
    }

    /**
     * @return bool
     */
    public function hasAttachments()
    {
        return ($this->attachments !== [] || $this->isAttachment());
    }

    /**
     * @return Part[]|Message[]
     */
    public function getAttachments()
    {
        if ($this->attachments === []) {
            return $this->isAttachment() ? [$this] : [];
        }
        return $this->attachments;
    }

    /**
     * @param Part $attachment
     * @return $this
     */
    public function setAttachment(Part $attachment)
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * @return Part[]
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @param Part $part
     * @return $this
     */
    public function setPart(Part $part)
    {
        $this->parts[] = $part;
        return $this;
    }

    /**
     * @param string $mime text/html or text/plain
     * @return Part[]
     */
    protected function findTextParts($mime = 'text/html')
    {
        $parts = [];
        if (
            $this->getMimeType() === $mime
            || ($this->getStream()->hasHeaders() && $this->getStream()->getMimeType() === $mime)
        ) {
            $parts[] = $this;
        }
        if (($boundary = $this->getBoundary()) === null) {
            return $parts;
        }
        $boundaries = [$boundary];
        foreach ($this->parts as $part) {
            if (in_array($part->getParentBoundary(), $boundaries) && ($type = $part->getMimeType()) !== null) {
                if ($type === 'multipart/alternative' || $type === 'multipart/related') {
                    if (($boundary = $part->getBoundary()) !== null) {
                        $boundaries[] = $boundary;
                    }
                    continue;
                }
                if (
                    $type === $mime
                    || ($part->getStream()->hasHeaders() && $part->getStream()->getMimeType() === $mime)
                ) {
                    $parts[] = $part;
                }
            }
        }
        return $parts;
    }

    /**
     * @param string $html
     * @return string
     */
    protected function replaceCid($html)
    {
        foreach (array_reverse($this->attachments) as $attachment) {
            /**
             * @var Part $attachment
             */
            if (
                ($id = $attachment->getID()) !== null
                && ($mime = $attachment->getMimeType()) !== null
                && strncasecmp($mime, 'image/', 6) === 0
                && strpos($html, "cid:{$id}") !== false
            ) {
                $html = str_replace("cid:{$id}", "data:{$mime};base64," . $attachment->getStream()->getBase64(), $html);
            }
        }
        return $html;
    }

}