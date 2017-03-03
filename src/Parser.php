<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email;

use bashkarev\email\parser\Email;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class Parser
{
    public static $buffer = 500000;
    public static $charset = 'UTF-8';

    /**
     * @var array MIME message class map
     */
    public static $map = [
        'message/feedback-report' => 'bashkarev\email\messages\Feedback'
    ];
    /**
     * @var array transport class map
     */
    public static $transport = [
        'url' => 'bashkarev\email\transports\Url',
        'local-file' => 'bashkarev\email\transports\Local',
        'anon-ftp' => 'bashkarev\email\transports\Ftp',
        'ftp' => 'bashkarev\email\transports\Ftp'
    ];

    /**
     * @param mixed $handles
     * @return Message
     */
    public static function email($handles)
    {
        $parser = new Email();
        return $parser->parse($handles);
    }

}
