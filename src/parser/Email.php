<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\parser;

use bashkarev\email\helpers\HeaderHelper;
use bashkarev\email\Message;
use bashkarev\email\Parser;
use bashkarev\email\Part;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Email
{
    const T_UNDEFINED = 0x00;
    const T_START_BOUNDARY = 0x01;
    const T_END_BOUNDARY = 0x02;
    const T_HEADER = 0x03;

    /**
     * @var Message
     */
    protected $message;
    /**
     * @var Part
     */
    protected $part;
    /**
     * @var resource
     */
    protected $handle;
    /**
     * @var string
     */
    protected $line;
    /**
     * @var array
     */
    protected $boundary = [];
    /**
     * @var bool
     */
    protected $allowedHeader = true;

    public function __construct($mime = null)
    {
        $class = ($mime !== null && isset(Parser::$map[$mime])) ? Parser::$map[$mime] : 'bashkarev\email\Message';
        $this->message = new $class();
    }

    /**
     * @param resource|string|array $handles
     * @param boolean $afterClose
     * @return Message
     */
    public function parse($handles, $afterClose = true)
    {
        $handles = (array)$handles;
        foreach ($handles as $handle) {
            if (!is_resource($handle)) {
                $this->handle = fopen('php://memory', 'rb+');
                fwrite($this->handle, $handle);
            } else {
                $this->handle = $handle;
            }
            rewind($this->handle);
            while (feof($this->handle) === false) {
                $this->read();
            }
            if ($afterClose) {
                fclose($this->handle);
            }
            $this->handle = null;
            $this->allowedHeader = true;
        }
        $this->insertPart();

        return $this->message;
    }

    /**
     * read line
     */
    protected function read()
    {
        $line = $this->nextLine();
        if (
            $this->parseBoundary($line) === true
            || $this->parseHeader($line) === true
        ) {
            return;
        }
        $this->parseContent($line);
    }

    /**
     * @param int $type
     * @param $value
     */
    protected function setToken($type, $value)
    {
        $this->bindHeader($type, $value);
        $this->bindBoundary($type, $value);
    }

    /**
     * @return string
     */
    protected function nextLine()
    {
        $this->line = fgets($this->handle);
        return rtrim($this->line, "\n\r");
    }

    /**
     * @param $line
     * @return bool
     */
    protected function parseBoundary($line)
    {
        if (!isset($line[0]) || $line[0] !== '-') {
            return false;
        }
        $line = rtrim($line);
        if (!isset($this->boundary[$line])) {
            return false;
        }
        $boundary = $this->boundary[$line];
        $this->setToken($boundary[0], $boundary[1]);
        return true;
    }

    /**
     * @param int $type
     * @param mixed $value
     */
    protected function bindBoundary($type, $value)
    {
        if (
            $type === self::T_HEADER
            && strcasecmp($value[0], 'Content-Type') === 0
            && preg_match('/boundary(?:=|\s=)([^;]+)/i', $value[1], $out)
        ) {
            $id = trim($out[1], " \t\"'");
            $this->boundary['--' . $id] = [self::T_START_BOUNDARY, $id];
            $this->boundary['--' . $id . '--'] = [self::T_END_BOUNDARY, $id];
        } else if ($type === self::T_START_BOUNDARY) {
            $this->allowedHeader = true;
            $this->insertPart();
            $this->part = new Part($value);
        }
    }

    /**
     * @param string $line
     * @return bool
     */
    protected function parseContent($line)
    {
        if ($this->allowedHeader === true) {
            return false;
        }

        /**
         * @var $stream \bashkarev\email\Transport
         */
        $stream = $this->context()->getStream();
        if ($line !== '') { // start EOL
            $stream->write($this->line);
        }

        $offset = ftell($this->handle);
        $foundSeparator = false;
        while (feof($this->handle) === false) {
            $before = ftell($this->handle);
            $buff = stream_get_line($this->handle, Parser::$buffer, "\n-");
            $after = ftell($this->handle);
            if (isset($buff[0]) && $buff[0] === '-') {
                $stream->write("\n");
                fseek($this->handle, $offset);
                break 1;
            }
            if ($foundSeparator) {
                $stream->write("\n-");
            }
            $stream->write($buff);
            $foundSeparator = $after - $before - strlen($buff) === 2;
            $offset = $after - 1;
        }
        return true;
    }

    /**
     * @param string $line
     * @return bool
     */
    protected function parseHeader($line)
    {
        if (
            $this->allowedHeader === false
            || strpos($line, ':') === false
        ) {
            return false;
        }
        list($field, $value) = HeaderHelper::parse($line);
        $i = ftell($this->handle);
        while (feof($this->handle) === false) {
            $line = $this->nextLine();
            if (
                ($this->allowedHeader = ($line !== '')) === false
                || ($line[0] !== "\t" && $line[0] !== ' ')
            ) {
                break 1;
            }
            $i = ftell($this->handle);
            $value .= ' ' . ltrim($line);
        }
        fseek($this->handle, $i);
        $value = $this->decodeMimeHeader($value);
        $this->setToken(self::T_HEADER, [$field, $value]);
        return true;
    }

    /**
     * @param int $type
     * @param mixed $value
     */
    protected function bindHeader($type, $value)
    {
        if ($type === self::T_HEADER) {
            $this->context()->setHeader($value[0], $value[1]);
        }
    }

    /**
     * @param $str
     * @return string
     */
    protected function decodeMimeHeader($str)
    {
        if (strpos($str, '=?') === false) {
            return $str;
        }
        $value = mb_decode_mimeheader($str);
        if (strpos($str, '?Q') !== false) {
            $value = str_replace('_', ' ', $value);
        }
        return $value;
    }

    /**
     * @return Message|Part
     */
    private function context()
    {
        return ($this->part === null) ? $this->message : $this->part;
    }

    private function insertPart()
    {
        if ($this->part !== null) {
            $this->part->isAttachment() ? $this->message->setAttachment($this->part) : $this->message->setPart($this->part);
            $this->part = null;
        }
    }

}