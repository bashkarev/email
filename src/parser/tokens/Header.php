<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\parser\tokens;

trait Header
{
    /**
     * @var bool
     */
    protected $allowedHeader = true;

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
        list($field, $value) = $this->lineHeader($line);
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
     * @param string $line
     * @return array
     */
    private function lineHeader($line)
    {
        $data = explode(':', $line);
        $filed = $data[0];
        if (isset($data[2])) {
            unset($data[0]);
            $value = implode(':', $data);
        } else {
            $value = $data[1];
        }

        return [
            rtrim($filed),
            ltrim($value)
        ];
    }
}