<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\Message;
use bashkarev\email\Mime;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class HeaderTest extends TestCase
{

    public function testConcatenating()
    {
        $this
            ->field('Subject: This is a test', 'This is a test', 'subject')
            ->field('Subject  : Saying Hello', 'Saying Hello', 'subject')
            ->field("Subject: This\r\n is a test\r\n  temp  ", 'This is a test temp', 'subject')
            ->field("Subject: This \r\nFri, 21 Nov 1997 09", 'This', 'subject')
            ->field("Subject: This \r\n\tFri, 21 Nov 1997 09(comment):   55  :  06 -0600\r\nUndefined:test", 'This  Fri, 21 Nov 1997 09(comment):   55  :  06 -0600', 'subject');
    }

    public function testEncode()
    {
        if (PHP_VERSION_ID < 50600) {
            return;
        }
        $this
            ->field('Subject: =?ISO-2022-JP?B?GyRCRnxLXDhsGyhC?=', '日本語', 'subject')
            ->field("Subject: =?ISO-2022-JP?B?GyRCRDkkJEQ5JCREOSQkGyhCU3ViamVjdBskQiROPmw5ZyRPGyhCZm9s?=\r\n\t=?ISO-2022-JP?B?ZGluZxskQiQ5JGskTiQsQDUkNyQkJHMkQCQxJEkkJCRDJD8kJCRJGyhC?=\r\n\t=?ISO-2022-JP?B?GyRCJCYkSiRrJHMkQCRtJCYhKRsoQg==?=", '長い長い長いSubjectの場合はfoldingするのが正しいんだけどいったいどうなるんだろう？', 'subject')
            ->field('Subject: =?ISO-8859-1?Q?Mail_avec_fichier_attach=E9_de_1ko?=', 'Mail avec fichier attaché de 1ko', 'subject')
            ->field('Subject: =?ISO-8859-1?Q?Informaci=F3n_Apartamento_a_la_Venta?= =?ISO-8859-1?Q?_en_Benasque(Demandas:_0442_______)?=,', 'Información Apartamento a la Venta en Benasque(Demandas: 0442       ),', 'subject')
            ->field("Subject: =?UTF-8?B?4p2E77iP77iPINCh0LDQvNGL0LUg0Y/RgNC60LjQtSDQsNC70YzQsdC+0Lw=?=\r\n\t=?UTF-8?B?0Ysg0Lgg0L/Qu9C10LnQu9C40YHRgtGLINGP0L3QstCw0YDRjyA=?=", '❄️️ Самые яркие альбомы и плейлисты января', 'subject')
            ->field("Subject: \r\n\t=?GB2312?B?zeLDs8jL1LG1xMD7xvejrMO/zOyw79b6xPrL0cv3xPqy+sa30NDStbXEyc/N?=\r\n\t=?GB2312?B?8snP087Ev7Hqv827p9fK1LSjoQ==?=", '外贸人员的利器，每天帮助您搜索您产品行业的上蛏嫌文勘昕突ё试矗', 'subject')
            //->field('Subject: =?iso-2022-jp?B?GyRCJygnJS1iGyhCNDEgGyRCJ2AnZBsoQiAyOC4wOS4yMDE2?=', 'ЖД№41 от 28.09.2016', 'subject') // Failed test
            ->field("To: =?UTF-8?Q?=D0=A1=D0=B5=D0=BB=D1=8E=D0=BA_=D0=A1=D1=82?=\n =?UTF-8?Q?=D0=B5=D0=BF=D0=B0=D0=BD_=D0=90=D0=BD=D0=B4?=\n =?UTF-8?Q?=D1=80=D0=B5=D0=B5=D0=B2=D0=B8=D1=87?= <stepan@selyuk.com>", '', 'subject')
            ->field('/m0066.eml', 'Окончание срока регистрации домена  - 2017-02-16', function (Message $message) {
                return $message->getSubject();
            });

    }

    public function testFindInHeader()
    {
        foreach ([
                     ['Content-Type', 'text/plain;charset="utf-8"', 'utf-8', 'normal'],
                     ['CONTENT-TYPE', 'TEXT/PLAIN;CHARSET="UTF-8"', 'UTF-8', 'uppercase'],
                     ['Content-Type', 'text/plain;charset=utf-8', 'utf-8', 'without quotes'],
                     ['Content-Type', "text/plain;charset='utf-8'", 'utf-8', 'quotes'],
                     ['Content-Type', 'text/plain;charset = utf-8 ', 'utf-8', 'with spaces'],
                     ['Content-Type', 'text/plain;charset  =  utf-8  ', 'utf-8', 'with spaces 2'],
                     ['Content-Type', "text/plain;charset\t=\tutf-8\t", 'utf-8', 'with tab'],
                 ] as $item) {
            list($header, $value, $expected, $message) = $item;
            $mime = new Mime();
            $mime->setHeader($header, $value);
            $this->assertEquals($expected, $mime->findInHeader('Content-Type', 'charset'), $message);
        }
    }

}