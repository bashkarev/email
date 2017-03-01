<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
abstract class Transport
{
    use HeadersTrait;

    /**
     * Transport constructor.
     * @param Mime $mime
     */
    abstract public function __construct(Mime $mime);

    /**
     * @return resource
     */
    abstract public function getHandle();

    /**
     * Close handle
     */
    abstract public function close();

    /**
     * @param $data
     * @return $this
     */
    abstract public function write($data);

    /**
     * @param resource $handle
     * @param null|int $length
     * @return int
     */
    public function copy($handle, $length = null)
    {
        if ($length === null) {
            $length = Parser::$buffer;
        }
        $mainHandle = $this->getHandle();
        rewind($mainHandle);
        while (feof($mainHandle) === false) {
            fwrite($handle, fread($mainHandle, $length));
        }
        $i = ftell($handle);
        fclose($handle);
        return $i;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        ob_start();
        ob_implicit_flush(false);
        $this->copy(fopen('php://output', 'cb'));
        return ob_get_clean();
    }

    /**
     * @return string
     */
    public function getBase64()
    {
        return base64_encode($this->getContents());
    }

}