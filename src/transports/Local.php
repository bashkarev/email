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
class Local extends Transport
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var resource
     */
    protected $handle;

    /**
     * @inheritdoc
     */
    public function __construct(Mime $mime)
    {
        $this->name = $mime->findInHeader('content-type', 'name');
        if ($this->name === null) {
            throw new \Exception('Required option name not found');
        }
    }

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if ($this->handle === null) {
            $this->handle = fopen($this->name, 'rb+');
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


}