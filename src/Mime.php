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
    /**
     * @var string
     */
    public $boundary;
    /**
     * @var array Map of all registered headers, as original name => array of values
     */
    private $headers = [];
    /**
     * @var array Map of lowercase header name => original name at registration
     */
    private $headerNames = [];
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
     * @return Stream
     */
    public function getStream()
    {
        if ($this->stream === null) {
            $this->stream = new Stream($this);
        }
        return $this->stream;
    }

    /**
     * @return bool
     */
    public function hasHeaders()
    {
        return $this->headers !== [];
    }

    /**
     * @param string $header
     * @param string $value
     */
    public function setHeader($header, $value)
    {
        $value = $this->trimHeaderValues(explode(';', $value));
        $normalized = strtolower($header);
        if (isset($this->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $this->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $this->headerNames[$normalized] = $header;
            $this->headers[$header] = $value;
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $header
     * @return bool
     */
    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    /**
     * @param string $header
     * @return array
     */
    public function getHeader($header)
    {
        $header = strtolower($header);
        if (!isset($this->headerNames[$header])) {
            return [];
        }
        $header = $this->headerNames[$header];
        return $this->headers[$header];
    }

    /**
     * @param string $header
     * @param string $glue
     * @return string
     */
    public function getHeaderLine($header, $glue = ', ')
    {
        return implode($glue, $this->getHeader($header));
    }

    /**
     * @return string|null
     */
    public function getMimeType()
    {
        if ($this->hasHeader('content-type')) {
            return mb_strtolower($this->getHeader('content-type')[0]);
        }
        return null;
    }

    /**
     * @return null|string
     */
    public function getContentID()
    {
        if ($this->hasHeader('content-id')) {
            return trim(str_replace(['<', '>'], '', $this->getHeader('content-id')[0]));
        }
        return null;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        foreach ($this->getHeader('content-type') as $value) {
            if (strpos($value, 'charset') !== false) {
                return mb_strtoupper(str_replace(['charset', '"', ' ', '='], '', $value));
            }
        }
        return 'UTF-8';
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        foreach ($this->getHeader('content-type') as $head) {
            if (strncasecmp($head, 'name', 4) !== 0) {
                continue;
            }
            $name = str_replace(['name', '"', ' ', '='], '', $head);
            if ($this->getCharset() !== Parser::$charset) {
                mb_convert_encoding($name, Parser::$charset, $this->getCharset());
            }
            return $name;
        }
        return null;
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
        return (bool)$this->getStream()->onFilter(fopen($filename, 'wb'));
    }

    /**
     * @param array $values
     * @return array
     */
    private function trimHeaderValues($values)
    {
        $data = [];
        foreach ($values as $value) {
            $item = trim($value);
            if ($item !== '') {
                $data[] = $item;
            }
        }
        return $data;
    }
}