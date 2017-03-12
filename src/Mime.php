<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email;

use bashkarev\email\helpers\MimeHelper;
use bashkarev\email\parser\Email;
use bashkarev\email\helpers\RFC5987;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Mime
{
    use HeadersTrait;

    /**
     * @var Stream
     */
    private $stream;
    /**
     * @var Message
     */
    private $message;

    /**
     * @return Message|null
     */
    public function getMessage()
    {
        if (
            $this->message === null
            && $this->stream !== null
            && ($mime = $this->getMimeType()) !== null
            && strncmp($mime, 'message/', 8) === 0
        ) {
            $this->message = (new Email($mime))->parse($this->stream->getHandle(), false);
        }
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isAttachment()
    {
        foreach ($this->getHeader('content-disposition') as $head) {
            if (strncasecmp($head, 'attachment', 10) === 0) {
                return true;
            }
        }
        $mime = $this->getMimeType();
        return !($mime === null
            || $mime === 'text/plain'
            || $mime === 'text/html'
            || strncmp($mime, 'multipart/', 10) === 0);
    }

    /**
     * @return Transport|null
     * @throws \Exception
     */
    public function getStream()
    {
        if ($this->stream === null) {
            if ($this->getMimeType() === 'message/external-body') {
                if (
                    ($type = $this->getAccessType()) === null
                    || !isset(Parser::$transport[$type])
                ) {
                    throw new \Exception("Not Supported transport: {$type}");
                }
                $this->stream = new Parser::$transport[$type]($this);
            } else {
                $this->stream = new Stream($this);
            }
        }
        return $this->stream;
    }

    /**
     * @return null|string
     */
    public function getAccessType()
    {
        $type = $this->findInHeader('content-type', 'access-type');
        if ($type !== null) {
            return mb_strtolower($type);
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getID()
    {
        if ($this->hasHeader('content-id')) {
            return trim($this->getHeader('content-id')[0], " <>\t\n\r\0\x0B");
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getBoundary()
    {
        return $this->findInHeader('content-type', 'boundary');
    }

    /**
     * @return null|string
     */
    public function getEncoding()
    {
        if ($this->hasHeader('content-transfer-encoding')) {
            return mb_strtolower(trim($this->getHeader('content-transfer-encoding')[0]));
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return mb_strtoupper($this->findInHeader('content-type', 'charset', 'UTF-8'));
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        $name = $this->findInHeader('content-type', 'name');
        if (
            $name !== null
            && ($charset = $this->getCharset()) !== Parser::$charset
        ) {
            return mb_convert_encoding($name, Parser::$charset, $charset);
        }
        return $name;
    }

    /**
     * @param null|string $default
     * @return null|string
     */
    public function getFileName($default = null)
    {
        $name = RFC5987::filename($this->getHeader('content-disposition'));
        if ($name === null) {
            $name = $this->getName();
        }

        if ($name === null && $default !== null) {
            $name = $default;
        }

        if (
            $name !== null
            && ($mime = $this->getMimeType()) !== null
            && isset(MimeHelper::$types[$mime])
            && !preg_match('/\.\w+$/', $name)
        ) {
            //unless file extension
            $name .= '.' . MimeHelper::$types[$mime];
        }

        return $name;
    }

    /**
     * @param $filename
     * @return bool
     */
    public function save($filename)
    {
        return (bool)$this->getStream()->copy(fopen($filename, 'wb'));
    }
}