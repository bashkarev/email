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
 * @see https://tools.ietf.org/rfc/rfc2017.txt
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Url extends Transport
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @inheritdoc
     */
    public function __construct(Mime $mime)
    {
        $this->url = $mime->findInHeader('content-type', 'url');
        if ($this->url === null) {
            throw new \Exception('Required option URL not found');
        }
    }

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if ($this->handle === null) {
            $this->handle = fopen('php://temp', 'rb+');
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
        if (!extension_loaded('curl')) {
            throw new \Exception('extension curl not found');
        }

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_FILE, $this->handle);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);

        // check cURL error
        $error = curl_errno($ch);
        $message = curl_error($ch);

        if ($error > 0) {
            throw new \Exception("Curl error: #{$error}: {$message}");
        }

        curl_close($ch);
    }

}