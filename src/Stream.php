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
class Stream
{
    /**
     * @var string
     */
    protected $encoded;
    /**
     * @var string
     */
    protected $charset;
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @param Mime $mime
     */
    public function __construct(Mime $mime)
    {
        if ($mime->hasHeader('content-transfer-encoding')) {
            $this->encoded = mb_strtolower($mime->getHeaderLine('content-transfer-encoding')); //ToDo move to method
        }
        $this->charset = $mime->getCharset();
        $this->handle = fopen('php://temp', 'r+');
    }

    /**
     * Close handle
     */
    public function close()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * @param $data
     * @return $this
     */
    public function write($data)
    {
        fwrite($this->handle, $data);
        return $this;
    }

    /**
     * @param resource $handle
     * @param null|int $length
     * @return int
     */
    public function onFilter($handle, $length = null)
    {
        if ($length === null) {
            $length = Parser::$buffer;
        }
        switch ($this->encoded) {
            case 'base64':
                stream_filter_append($handle, 'convert.base64-decode');
                break;
            case 'quoted-printable':
                stream_filter_append($handle, 'convert.quoted-printable-decode');
                break;
        }
        rewind($this->handle);
        while (feof($this->handle) === false) {
            fwrite($handle, fread($this->handle, $length));
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
        $this->onFilter(fopen('php://output', 'c'));
        $contents = ob_get_clean();
        if ($this->charset !== Parser::$charset) {
            $contents = mb_convert_encoding($contents, Parser::$charset, $this->charset);
        }
        return $contents;
    }

    /**
     * @return string
     */
    public function getBase64()
    {
        if ($this->encoded === 'base64') {
            rewind($this->handle);
            return stream_get_contents($this->handle);
        } else {
            return base64_encode($this->getContents());
        }
    }

}