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
class Stream extends Transport
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
        $this->encoded = $mime->getEncoding();
        $this->charset = $mime->getCharset();
        $this->handle = fopen('php://temp', 'rb+');
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @inheritdoc
     */
    public function write($data)
    {
        fwrite($this->handle, $data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function copy($handle, $length = null)
    {
        switch ($this->encoded) {
            case 'base64':
                stream_filter_append($handle, 'convert.base64-decode');
                break;
            case 'quoted-printable':
                stream_filter_append($handle, 'convert.quoted-printable-decode');
                break;
        }
        return parent::copy($handle, $length);
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        $contents = parent::getContents();
        if ($this->charset !== Parser::$charset) {
            $contents = mb_convert_encoding($contents, Parser::$charset, $this->charset);
        }
        return $contents;
    }

    /**
     * @inheritdoc
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