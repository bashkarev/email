<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email;

use bashkarev\email\helpers\HeaderHelper;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
trait HeadersTrait
{
    /**
     * @var array Map of all registered headers, as original name => array of values
     */
    private $headers = [];
    /**
     * @var array Map of lowercase header name => original name at registration
     */
    private $headerNames = [];

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
     * @param string $type
     * @param mixed $default
     * @return mixed
     */
    public function findInHeader($header, $type, $default = null)
    {
        if ($this->headers === []) {
            return $default;
        }
        $len = strlen($type);
        foreach ($this->getHeader($header) as $head) {
            if (strncasecmp($head, $type, $len) === 0) {
                return trim(ltrim(substr($head, $len), " \t="), " \t\"'");
            }
        }
        return $default;
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
     * @param string $data
     */
    protected function parseHeader($data)
    {
        $data = str_replace(["\r\n", "\n\r", "\r"], "\n", $data);
        $lines = explode("\n", $data);
        for ($i = 0, $count = count($lines); $i < $count; $i++) {
            $i = $this->parseBlockHeader($lines, $count, $i);
            if ($i === false) {
                break;
            }
        }
    }

    /**
     * @param array $lines
     * @param int $count
     * @param int $current
     * @return mixed
     */
    protected function parseBlockHeader($lines, $count, $current)
    {
        $line = $lines[$current];
        if (strpos($line, ':') === false) {
            return false;
        }
        list($header, $value) = HeaderHelper::parse($line);
        for ($i = $current + 1; $i < $count; $i++) {
            $line = $line[$i];
            if ($line[0] !== "\t" && $line[0] !== ' ') {
                break;
            }
            $value .= ' ' . ltrim($line);
        }
        $this->setHeader($header, $value);
        return --$i;
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