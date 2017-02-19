<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\parser\tokens;

use bashkarev\email\Parser;
use bashkarev\email\Stream;

trait Content
{

    /**
     * @param string $line
     * @return bool
     */
    public function parseContent($line)
    {
        if ($this->allowedHeader === true) {
            return false;
        }

        /**
         * @var $stream Stream
         */
        $stream = $this->context()->getStream();
        if ($line !== '') { // start EOL
            $stream->write($this->line);
        }

        $offset = ftell($this->handle);
        while (feof($this->handle) === false) {
            $buff = stream_get_line($this->handle, Parser::$buffer, "\n-");
            if (@$buff[0] === '-') {
                $stream->write("\n");
                fseek($this->handle, $offset);
                break 1;
            }
            $stream->write($buff);
            $offset = ftell($this->handle) - 1;
        }
        return true;
    }

}