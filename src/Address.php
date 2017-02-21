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
class Address
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $email;

    /**
     * @return string
     */
    public function display()
    {
        return ($this->name === null) ? $this->email : $this->name;
    }

}