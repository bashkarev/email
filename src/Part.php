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
class Part extends Mime
{
    /**
     * @var string
     */
    private $_parentBoundary;

    /**
     * Part constructor.
     * @param string $parentBoundary
     */
    public function __construct($parentBoundary)
    {
        $this->_parentBoundary = $parentBoundary;
    }

    /**
     * @return null|string
     */
    public function getParentBoundary()
    {
        return $this->_parentBoundary;
    }

}