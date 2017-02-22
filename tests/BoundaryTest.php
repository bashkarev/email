<?php
/**
 * @copyright Copyright (c) 2017 Dmitriy Bashkarev
 * @license https://github.com/bashkarev/email/blob/master/LICENSE
 * @link https://github.com/bashkarev/email#readme
 */

namespace bashkarev\email\tests;

use bashkarev\email\parser\Email;

/**
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 */
class BoundaryTest extends TestCase
{

    public function testParse()
    {
        $expected = [
            '--te st' => [Email::T_START_BOUNDARY, 'te st'],
            '--te st--' => [Email::T_END_BOUNDARY, 'te st'],
        ];

        foreach ([
                     ["Content-Type: multipart/mixed;\n\tboundary=te st", 'normal'],
                     ["CONTENT-TYPE: MULTIPART/MIXED;\n\tBOUNDARY=te st", 'uppercase'],
                     ["content-type: multipart/mixed;\n\tboundary=te st", 'lowercase'],
                     ["Content-Type: multipart/mixed;\n\tboundary=te st\t", 'right tab'],
                     ["Content-Type: multipart/mixed;\n\tboundary=te st     ", 'right spaces'],
                     ["Content-type: multipart/mixed; boundary=\"te\n     st\"", 'new line'],
                     ["Content-Type: \"multipart/mixed\"; boundary=\"te st\"", '>"<'],
                     ["Content-Type: 'multipart/mixed'; boundary='te st'", ">'<"],
                 ] as $eml) {
            $parser = new Email();
            $parser->parse($eml[0]);
            $this->assertEquals($expected, $this->getProperty('bashkarev\email\parser\Email', 'boundary')->getValue($parser), $eml[1]);
        }

        $parser = new Email();
        $parser->parse('Content-Type: multipart/alternative; boundary=boundary42 ');
        $this->assertEquals([
            '--boundary42' => [Email::T_START_BOUNDARY, 'boundary42'],
            '--boundary42--' => [Email::T_END_BOUNDARY, 'boundary42'],
        ], $this->getProperty('bashkarev\email\parser\Email', 'boundary')->getValue($parser));

        $parser = new Email();
        $parser->parse('Content-Type: multipart/mixed; boundary=gc0pJq0M:08jU534c0p');
        $this->assertEquals([
            '--gc0pJq0M:08jU534c0p' => [Email::T_START_BOUNDARY, 'gc0pJq0M:08jU534c0p'],
            '--gc0pJq0M:08jU534c0p--' => [Email::T_END_BOUNDARY, 'gc0pJq0M:08jU534c0p'],
        ], $this->getProperty('bashkarev\email\parser\Email', 'boundary')->getValue($parser));

    }


}