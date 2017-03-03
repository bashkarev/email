<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\transports;

use bashkarev\email\Mime;
use bashkarev\email\Transport;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Ftp extends Transport
{
    /**
     * @var string
     */
    public $username = 'anonymous';
    /**
     * @var string
     */
    public $password;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var resource
     */
    protected $handle;
    /**
     * @var string
     */
    protected $site;

    /**
     * @inheritdoc
     */
    public function __construct(Mime $mime)
    {
        $this->path = $mime->findInHeader('content-type', 'directory', '');
        $this->path = '/' . $mime->findInHeader('content-type', 'name', '');
        $this->site = $mime->findInHeader('content-type', 'site');
    }

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if ($this->handle === null) {
            $this->handle = fopen('php://temp', 'wb+');
            $this->download();
        }
        return $this->handle;
    }

    /**
     * @inheritdoc
     */
    public function write($data)
    {
        $this->parseHeader($data);
        return $this;
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
     * @throws \Exception
     */
    protected function download()
    {
        if (!extension_loaded('ftp')) {
            throw new \Exception('PHP extension FTP is not loaded.');
        }
        $stream = ftp_connect($this->site);
        ftp_login($stream, $this->username, $this->password);
        ftp_pasv($stream, true);
        ftp_fget($stream, $this->handle, $this->path, FTP_BINARY);
    }

}