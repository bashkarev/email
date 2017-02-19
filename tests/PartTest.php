<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/eamil/blob/master/LICENSE
 * @link https://github.com/bashkarev/eamil#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Part;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class PartTest extends TestCase
{


    public function testName()
    {
        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', 'attachment;filename*=utf-8\'\'%D1%80%D0%B5%D0%BA%D0%B2%D0%B8%D0%B7%D0%B8%D1%82%D1%8B%2D%D0%9A%D0%BE%D0%BD%D1%81%D1%83%D0%BB%D1%8C%D1%82%D0%B0%D0%BD%D1%82%20%D0%98%D0%A2%20%2D%20new.doc');
        $this->assertEquals('реквизиты-Консультант ИТ - new.doc', $part->getName());


        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', "attachment; filename=\"EURO rates\"; filename*=utf-8''%e2%82%ac%20rates");
        $this->assertEquals('€ rates', $part->getName());

        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', "attachment; filename*0*=utf-8''nyan%20cat%20%E2%9C%94.gif");
        $this->assertEquals('nyan cat ✔.gif', $part->getName());


        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', "attachment; filename*0*=UTF-8''long%20name%20with%20spaces,%20very%20very%20very%20very;filename*1*=%20long%20long%20long%20long.txt");
        $this->assertEquals('long name with spaces, very very very very long long long long.txt', $part->getName());

        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', "attachment; filename=simple.txt");
        $this->assertEquals('simple.txt', $part->getName());

        $part = new Part('tempId');
        $part->setHeader('Content-Disposition', 'attachment; filename="simple.txt"');
        $this->assertEquals('simple.txt', $part->getName());

        $part = new Part('tempId');
        $part->setHeader('Content-Type', 'message/rfc822');
        $this->assertEquals('message.eml', $part->getName());

    }


}