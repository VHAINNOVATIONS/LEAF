<?php

declare(strict_types = 1);
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

include '../../libs/php-commons/XSSHelpers.php';

use PHPUnit\Framework\TestCase;

/**
 * Tests libs/php-commons/XSSHelpers.php
 */
final class XSSHelpersTest extends TestCase
{
    /**
     * Tests XSSHelpers::sanitizer($in, $allowedTags)
     *
     * Tests sanitizing HTML with anchor elements.
     */
    public function testSanitizer_links() : void
    {
        $in1 = "<a href='http://google.com'>Google</a>";
        $in2 = "<a href='#' onclick='alert(\"gotcha\")'>Hello</a>";

        $this->assertEquals(
            '&lt;a href=&#039;http://google.com&#039;&gt;Google</a>',
            XSSHelpers::sanitizer($in1, array('a'))
        );
        $this->assertEquals(
            '&lt;a href=&#039;#&#039; onclick=&#039;alert(&quot;gotcha&quot;)&#039;&gt;Hello</a>',
            XSSHelpers::sanitizer($in2, array('a'))
        );

        $this->assertEquals(
            '&lt;a href=&#039;#&#039; onclick=&#039;alert(&quot;gotcha&quot;)&#039;&gt;Hello&lt;/a&gt;',
            XSSHelpers::sanitizer($in2, array())
        );
        $this->assertEquals(
            '&lt;a href=&#039;http://google.com&#039;&gt;Google&lt;/a&gt;',
            XSSHelpers::sanitizer($in1, array(''))
        );
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests processing line breaks (\n, \r) within the HTML input
     */
    public function testSanitizeHTML_LineBreaks() : void
    {
        $linebreaks = "text\nwith\nbreaks";
        $linebreaks2 = "text\rwith\rbreaks";
        $linebreaks3 = "text\r\nwith\r\nbreaks";
        $linebreaks4 = "text\n\rwith\n\rbreaks";
        $linebreaks5 = "text\n\nwith\n\nbreaks";

        $expectedOutput = 'text<br>with<br>breaks';
        $expectedOutput2 = 'text<br><br>with<br><br>breaks';

        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks2));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks3));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks4));

        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($linebreaks5));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests processing line breaks (\n, \r) in a paragraph (<p>) within the HTML input
     */
    public function testSanitizeHTML_LineBreaks_Paragraphs() : void
    {
        $str1 = "<p>text\nwith\nbreaks\nin\nparagraph</p>";
        $out1 = '<p>textwithbreaksinparagraph</p>';
        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests the length of the HTML input
     */
    public function testSanitizeHTML_Length() : void
    {
        $shortStr = str_repeat('.', 12345);
        $maxStr = str_repeat('.', 65535);
        $tooLongStr = str_repeat('.', 70000);

        $this->assertEquals(12345, strlen(XSSHelpers::sanitizeHTML($shortStr)));
        $this->assertEquals(65535, strlen(XSSHelpers::sanitizeHTML($maxStr)));

        // Any string over 65535 should be shortened to 65535
        $this->assertEquals(65535, strlen(XSSHelpers::sanitizeHTML($tooLongStr)));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Ordered Lists within the HTML input
     */
    public function testSanitizeHTML_OL() : void
    {
        $str1 = '<ol><li>an</li><li>ordered</li><li>list</li></ol>';
        $out1 = '<ol><li>an</li><li>ordered</li><li>list</li></ol>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests scrubbing SSNs
     */
    public function testSanitizeHTML_SSN() : void
    {
        $str1 = '123-45-6789';
        $out1 = '###-##-####';

        $str2 = '<tr><td>123-45-6789</td></tr>';
        $out2 = '<tr><td>###-##-####</td></tr>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
        $this->assertEquals($out2, XSSHelpers::sanitizeHTML($str2));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Tables within the HTML input
     */
    public function testSanitizeHTML_Table() : void
    {
        $str1 = '<table><tr><td></td></tr></table>';
        $out1 = '<table class="table"><tr><td></td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests formatting tabs (<b><i><u>) within the HTML input
     */
    public function testSanitizeHTML_TextFormatting() : void
    {
        $str1 = '<b>Some <i>formatted</i> <u>text</u>.'; // closing </b> left out intentionally
        $out1 = '<b>Some <i>formatted</i> <u>text</u>.</b>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests any unclosed element tags within the HTML input
     */
    public function testSanitizeHTML_UnclosedTags() : void
    {
        $str1 = '<table><tr><td><p><b>unclosed<i>tags';
        $out1 = '<table class="table"><tr><td><p><b>unclosed<i>tags</i></b></p></td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests any unclosed inner element tags within the HTML input
     */
    public function testSanitizeHTML_UnclosedTags_Inner() : void
    {
        $str1 = '<table><tr><td>unclosed inner tr</td></table>';
        $out1 = '<table class="table"><tr><td>unclosed inner tr</td></tr></table>';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTML()
     *
     * Tests Unordered Lists within the HTML input
     */
    public function testSanitizeHTML_UL() : void
    {
        $str1 = '<ul><li>an</li><li>unordered</li><li>list</li></ul>';
        $out1 = '&lt;ul&gt;<li>an</li><li>unordered</li><li>list</li>&lt;/ul&gt;';

        $this->assertEquals($out1, XSSHelpers::sanitizeHTML($str1));
    }

    /**
     * Tests XSSHelpers::sanitizeHTMLRich()
     *
     * Tests processing images within the HTML input
     */
    public function testSanitizeHTMLRich_Img() : void
    {
        $img = '<img src="hello.jpg">';
        $img2 = '<img src="hello.jpg"/>';
        $img3 = '<img src="hello.jpg" />';
        $img4 = '<img src="hello.jpg" alt="world">';
        $img5 = '<img src="hello.jpg" alt="world"/>';
        $img6 = '<img src="hello.jpg" alt="world" />';
        $img7 = '<img src="javascript:alert(\'hello.jpg\')">';

        $expectedOutput = '<img src="hello.jpg" alt="" />"';
        $expectedOutput2 = '<img src="hello.jpg" alt="world" />"';
        $expectedOutput3 = '&lt;img src=&quot;javascript:alert(&#039;hello.jpg&#039;)&quot;&gt;';

        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img2));
        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img3));

        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img4));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img5));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img6));

        $this->assertEquals($expectedOutput3, XSSHelpers::sanitizeHTML($img7));
    }

    /**
     * Tests XSSHelpers::xscrub()
     *
     * Tests escaping HTML tags
     */
    public function testXscrub_tags() : void
    {
        $str1 = '<table><tr><td></td></tr></table>';
        $out1 = '&lt;table&gt;&lt;tr&gt;&lt;td&gt;&lt;/td&gt;&lt;/tr&gt;&lt;/table&gt;';

        $this->assertEquals($out1, XSSHelpers::xscrub($str1));
    }

    /**
     * Tests XSSHelpers::xscrub()
     *
     * Tests escaping HTML tags
     */
    public function testXscrub_InlineJS() : void
    {
        $str1 = "<a onmouseover=\"alert('xss')\">test</a>";
        $out1 = '&lt;a onmouseover=&quot;alert(&apos;xss&apos;)&quot;&gt;test&lt;/a&gt;';

        $this->assertEquals($out1, XSSHelpers::xscrub($str1));
    }

    /**
     * Tests XSSHelpers::scrubNewLinesFromURL()
     *
     * Tests scrubbing newline characters from URL
     */
    public function testScrubNewLinesFromURL() : void
    {
        $goodNames = array('http://www.website.com',
                            'http://www.website.eu.com',
                            'http://www.sub.website.com',
                            'https://www.website.com',
                            'https://www.website.eu.com',
                            'https://www.sub.website.com',
                            'http://www.website.com/whatever',
                            'http://www.website.eu.com/what.exe',
                            'http://www.sub.website.com/yo/yo.jpg',
                            'http://www.sub.123website.com/yo/yo.jpg',
                        );

        $badNames = array('http://w%0aww.%0awebsit%0ae.com' => 'http://www.website.com',
                          'http://%0Awww%0A.webs%0Aite.eu%0A.co%0Am' => 'http://www.website.eu.com',
                          'http://\r\nwww.sub.website.com' => 'http://www.sub.website.com',
                          'https://\nwww.websi\rte.com' => 'https://www.website.com',
                          'https://w%0A%0A%0Aww.web%0Asite.eu%0A.com' => 'https://www.website.eu.com',
                          'https://%0a%0a%0awww.sub%0a%0a.websi%0a%0ate.com' => 'https://www.sub.website.com',
                          'http://www.website.com/whatever' => 'http://www.website.com/whatever',
                          'http://www.websi\r\n\r\n\r\nte.eu.com/what.exe' => 'http://www.website.eu.com/what.exe',
                          'http://w\r\n\r\nww.sub.website.com/\r\nyo/\r\nyo.jp\r\ng' => 'http://www.sub.website.com/yo/yo.jpg',
                        );

        foreach($goodNames as $good)
        {
            $this->assertEquals($good, XSSHelpers::scrubNewLinesFromURL($good));
        }

        foreach($badNames as $bad => $good)
        {
            $this->assertEquals($good, XSSHelpers::scrubNewLinesFromURL($bad));
        }
    }

    /**
     * Tests XSSHelpers::scrubFilename()
     *
     * Tests scrubbing bad characters from filename
     */
    public function testScrubFilename() : void
    {
        $goodNames = array('filename.exe',
                            'file-name.exe',
                            'file_name.exe',
                            '_filename_.exe',
                            '-file-name-.exe',
                            '_-file_name-_.exe',
                            'filename132.exe',
                            '456file-name.exe',
                            'file4_na5me.exe',
                            '_fil785ename_.exe',
                            '-fi448ame-.exe',
                            '_-f48748name-_.exe',
                        );

        $badNames = array('badpath/filename.exe' => 'badpathfilename.exe',
                            'badpath//file-name.exe' => 'badpathfile-name.exe',
                            'badpath///file_name.exe' => 'badpathfile_name.exe',
                            'badpath\\filename.exe' => 'badpathfilename.exe',
                            'badpath\\\\file-name.exe' => 'badpathfile-name.exe',
                            'badpath\\\\\\file_name.exe' => 'badpathfile_name.exe',
                            '!@#$%^&_filename_.exe' => '_filename_.exe',
                            '-f)*&^%ile-name-.exe' => '-file-name-.exe',
                            '_-file_n[]}{\';";/?><$ame-_.exe' => '_-file_name-_.exe',
                            '123badpath/filename.exe' => '123badpathfilename.exe',
                            'badpath//file-name123.exe' => 'badpathfile-name123.exe',
                            'badpath///file_name.e123xe' => 'badpathfile_name.e123xe',
                            'badpath\\312filename.exe' => 'badpath312filename.exe',
                            'badpath\\33\\file-name.exe' => 'badpath33file-name.exe',
                            'badpath44\\\\\\file_name.exe' => 'badpath44file_name.exe',
                            '!@#345$%^&_filename_.exe' => '345_filename_.exe',
                        );

        foreach($goodNames as $good)
        {
            $this->assertEquals($good, XSSHelpers::scrubFilename($good));
        }

        foreach($badNames as $bad => $good)
        {
            $this->assertEquals($good, XSSHelpers::scrubFilename($bad));
        }

        
    }
}
