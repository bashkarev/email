<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email;

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
     * @return bool
     */
    public function isAttachment()
    {
        $mime = $this->getMimeType();
        if (
            $mime === 'message/rfc822'
            || $mime === 'application/octet-stream'
        ) {
            return true;
        }
        foreach ($this->getHeader('content-disposition') as $head) {
            if (strncasecmp($head, 'filename', 8) === 0) {
                return true;
            }
        }
        return false;
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
     * @param $header
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
     * @return string
     */
    public function getMimeType()
    {
        if ($this->hasHeader('content-type')) {
            return mb_strtolower($this->getHeader('content-type')[0]);
        }
        return 'text/plain';
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
        ///
        $name = null;
        $charset = null;
        $encode = null;
        foreach ($this->getHeader('content-disposition') as $head) {
            if (strncasecmp($head, 'filename', 8) !== 0) {
                continue;
            }
            if ($head[8] === '*') {
                if (preg_match('/filename\*(0\*|)=([^\']+)\'\'(.*)/', $head, $out)) {
                    $charset = mb_strtoupper($out[2]);
                    $encode = $out[3];
                } else {
                    $encode .= preg_replace('/filename\*\d+\*\=/', '', $head);
                }
            } else {
                $name = str_replace(['filename', '"', ' ', '='], '', $head);
            }
        }

        if ($encode !== null) {
            $name = urldecode($encode);
            if ($charset !== Parser::$charset) {
                $name = mb_convert_encoding($name, Parser::$charset, $charset);
            }
        }
        //

        foreach ($this->getHeader('content-type') as $head) {
            if (strncasecmp($head, 'name', 4) !== 0) {
                continue;
            }
            $name = str_replace(['name', '"', ' ', '='], '', $head);
            if ($this->getCharset() !== Parser::$charset) {
                mb_convert_encoding($name, Parser::$charset, $this->getCharset());
            }
        }


        if ($name === null) {
            $name = $this->createName();
        }
        return $name;
    }

    /**
     * @param $filename
     * @return bool
     */
    public function save($filename)
    {
        return (bool)$this->getStream()->onFilter(fopen($filename, 'w'));
    }

    /**
     * @return string
     */
    protected function createName()
    {
        if ($this->getMimeType() === 'message/rfc822') {
            return 'message.eml';
        }
        return 'unknown.eml';
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