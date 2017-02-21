<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\parser;

use bashkarev\email\Message;
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

    use tokens\Boundary;
    use tokens\Header;
    use tokens\Content;

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
     * @param resource|string $handle
     * @return Message
     */
    public function parse($handle)
    {
        if (!is_resource($handle)) {
            $this->handle = fopen('php://memory', 'r+');
            fwrite($this->handle, $handle);
        } else {
            $this->handle = $handle;
        }
        rewind($this->handle);
        $this->message = new Message();
        while (feof($this->handle) === false) {
            $this->read();
        }
        $this->insertPart();
        fclose($this->handle);
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