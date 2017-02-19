<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\parser\tokens;

use bashkarev\email\Part;

trait Boundary
{
    /**
     * @var array
     */
    protected $boundary = [];

    /**
     * @param $line
     * @return bool
     */
    protected function parseBoundary($line)
    {
        if (@$line[0] !== '-') {
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
            && $value[0] === 'Content-Type' //
            && preg_match('/boundary(?:=|\s=)([^;]+)/i', $value[1], $out)
        ) {
            $id = trim(str_replace('"', '', $out[1]));
            $this->boundary['--' . $id] = [self::T_START_BOUNDARY, $id];
            $this->boundary['--' . $id . '--'] = [self::T_END_BOUNDARY, $id];
        } else if ($type === self::T_START_BOUNDARY) {
            $this->allowedHeader = true;
            $this->context()->boundary = $value;
            $this->insertPart();
            $this->part = new Part($value);
        }
    }

}