<?php

declare(strict_types = 1);
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

if (!class_exists('XSSHelpers'))
{
    include __DIR__.'/../../../../libs/php-commons/XSSHelpers.php';
}

use PHPUnit\Framework\TestCase;

/**
 * Tests libs/php-commons/XSSHelpers.php
 * @covers \XSSHelpers
 */
final class XSSHelpersTest extends TestCase
{
    /**
     *  @covers \XSSHelpers::sanitizer
     *  Tests sanitizing HTML with anchor elements.
     */
    public function testSanitizer_links() : void
    {
        $in1 = "<a href='https://google.com'>Google</a>";
        $in2 = "<a href='#' onclick='alert(\"gotcha\")'>Hello</a>";

        $this->assertEquals(
            '<a href="https://google.com" target="_blank">Google</a>',
            XSSHelpers::sanitizer($in1, array('a'))
        );
        $this->assertEquals(
            '<a href="#" target="_blank">Hello</a>',
            XSSHelpers::sanitizer($in2, array('a'))
        );

        $this->assertEquals(
            '&lt;a href=\'#\' onclick=\'alert("gotcha")\'&gt;Hello&lt;/a&gt;',
            XSSHelpers::sanitizer($in2, array())
        );
        $this->assertEquals(
            '&lt;a href=\'https://google.com\'&gt;Google&lt;/a&gt;',
            XSSHelpers::sanitizer($in1, array(''))
        );
    }

    /**
     * @covers \XSSHelpers::sanitizeHTML
     * Tests processing line breaks (\n, \r) within the HTML input
     */
    public function testSanitizeHTML_LineBreaks() : void
    {
        $linebreaks = "text\nwith\nbreaks";
        $linebreaks2 = "text\rwith\rbreaks";
        $linebreaks3 = "text\r\nwith\r\nbreaks";
        $linebreaks4 = "text\n\rwith\n\rbreaks";
        $linebreaks5 = "text\n\nwith\n\nbreaks";

        $expectedOutput  = "text<br />\nwith<br />\nbreaks";
        $expectedOutput2 = "text<br />\rwith<br />\rbreaks";
        $expectedOutput3 = "text<br />\r\nwith<br />\r\nbreaks";
        $expectedOutput4 = "text<br />\n\rwith<br />\n\rbreaks";
        $expectedOutput5 = "text<br /><br />\nwith<br /><br />\nbreaks";

        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($linebreaks));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($linebreaks2));
        $this->assertEquals($expectedOutput3, XSSHelpers::sanitizeHTML($linebreaks3));
        $this->assertEquals($expectedOutput4, XSSHelpers::sanitizeHTML($linebreaks4));
        $this->assertEquals($expectedOutput5, XSSHelpers::sanitizeHTML($linebreaks5));
    }

    /**
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTML
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
     * @covers \XSSHelpers::sanitizeHTMLRich
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

        $expectedOutput = '&lt;img src="hello.jpg"&gt;';
        $expectedOutput2 = '&lt;img src="hello.jpg"/&gt;';
        $expectedOutput3 = '&lt;img src="hello.jpg" /&gt;';
        $expectedOutput4 = '&lt;img src="hello.jpg" alt="world"&gt;';
        $expectedOutput5 = '&lt;img src="hello.jpg" alt="world"/&gt;';
        $expectedOutput6 = '&lt;img src="hello.jpg" alt="world" /&gt;';
        $expectedOutput7 = '&lt;img src="javascript:alert(\'hello.jpg\')"&gt;';

        $this->assertEquals($expectedOutput, XSSHelpers::sanitizeHTML($img));
        $this->assertEquals($expectedOutput2, XSSHelpers::sanitizeHTML($img2));
        $this->assertEquals($expectedOutput3, XSSHelpers::sanitizeHTML($img3));
        $this->assertEquals($expectedOutput4, XSSHelpers::sanitizeHTML($img4));
        $this->assertEquals($expectedOutput5, XSSHelpers::sanitizeHTML($img5));
        $this->assertEquals($expectedOutput6, XSSHelpers::sanitizeHTML($img6));
        $this->assertEquals($expectedOutput7, XSSHelpers::sanitizeHTML($img7));
    }

    /**
     * @covers \XSSHelpers::xscrub
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
     * @covers \XSSHelpers::scrubNewLinesFromURL
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
     * @covers XSSHelpers::scrubFilename
     *
     * Tests scrubbing bad characters from filename
     */
    public function testScrubFilename() : void
    {
        $goodNames = array('file\'name.exe',
                            'file-name.exe',
                            'file_name.exe',
                            '_filename_.exe',
                            '-file-name-.exe',
                            '_-file_name-_.exe',
                            'filename132.exe',
                            '456file-name.exe',
                            'file4_na5me.exe',
                            '_\'fil785ename_.exe',
                            '-fi448\'ame-.exe',
                            '_-f48748name-_.exe',
                            'f\'\'\'ile name.exe',
                            'fi le-name.exe',
                            'file_nam e.exe',
                            '_filen ame_.exe',
                            '-file-n ame\'-.exe',
                            '_ -file_name-_.exe',
                            'filename132 .exe',
                            '456    file- name.exe',
                            'file4 _na5me.exe',
                            '_fil785\'ename_.exe',
                            '-fi448 ame-.exe',
                            '_- f48\'7    48name-_.exe',
                        );

        $badNames = array('badpath/filename.exe' => 'badpathfilename.exe',
                            'bad"pa>>th//file-na\'me.exe' => 'badpathfile-na\'me.exe',
                            'badp<<ath///file_name.exe' => 'badpathfile_name.exe',
                            'ba>d"\'path\\f*ile*name.exe' => 'bad\'pathfilename.exe',
                            'b|adpath:\\\\file-name.e|xe' => 'badpathfile-name.exe',
                            'badpa<<<th\\\\\:file_name.exe' => 'badpathfile_name.exe',
                            '!@#$%^&_fil*ename_.exe' => '!@#$%^&_filename_.exe',
                            '-f)*&^%il:e-name-.ex"e' => '-f)&^%ile-name-.exe',
                            '_-file_n[]*}{\';";/?><$ame-_.exe' => '_-file_n[]}{\';;$ame-_.exe',
                            '123badp>>>ath/filenam"e.exe' => '123badpathfilename.exe',
                            'badpa\'th//file-name123.exe' => 'badpa\'thfile-name123.exe',
                            'badpa"th///file_na<<<<<me".e123xe' => 'badpathfile_name.e123xe',
                            'badp\'ath\\*312filena"me.exe' => 'badp\'ath312filename.exe',
                            '|b>>>>ad:path\\33\\file-name.exe' => 'badpath33file-name.exe',
                            'badpa"|th44*\\\\\\fi<le_name.exe' => 'badpath44file_name.exe',
                            '!@#345$%^&_filename_|.exe' => '!@#345$%^&_filename_.exe',
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

    /**
     * @covers \XSSHelpers::scrubObject
     *
     * Tests escaping everything in an object or array
     */
    public function testScrubObject() : void
    {
        $obj = (object)[1 => 1,2 => 2,3 => 3];
        $array = [1 => 1,2 => 2,3 => 3];

        $obj2 = (object)[1 => [1 => 1,2 => 2,3 => 3], 2 => 2];
        $array2 = [1 => [1 => 1,2 => 2,3 => 3], 2 => 2];

        $obj3 = (object)[1 => 'one',2 => 'two',3 => 'three'];
        $array3 = [1 => 'one',2 => 'two',3 => 'three'];

        $obj4 = (object)[1 => [1 => 'one',2 => 'two',3 => 'three'], 2 => 'two'];
        $array4 = [1 => [1 => 'one',2 => 'two',3 => 'three'], 2 => 'two'];

        $obj5 = (object)[1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => 'three'], 2 => '<h1>two</h1>'];
        $array5 = [1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => 'three'], 2 => '<h1>two</h1>'];
        $obj5Scrubbed = (object)[1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => 'three'], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];
        $array5Scrubbed = [1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => 'three'], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];

        $obj6 = (object)[1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => ["something" => '<style>other</style>']], 2 => '<h1>two</h1>'];
        $array6 = [1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => ["something" => '<style>other</style>']], 2 => '<h1>two</h1>'];
        $obj6Scrubbed = (object)[1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => ["something" => '&lt;style&gt;other&lt;/style&gt;']], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];
        $array6Scrubbed = [1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => ["something" => '&lt;style&gt;other&lt;/style&gt;']], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];

        $obj7 = (object)[1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => (object)["something" => '<style>other</style>']], 2 => '<h1>two</h1>'];
        $array7 = [1 => [1 => '<script>one</script>',2 => '<b>two</b>',3 => (object)["something" => '<style>other</style>']], 2 => '<h1>two</h1>'];
        $obj7Scrubbed = (object)[1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => (object)["something" => '&lt;style&gt;other&lt;/style&gt;']], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];
        $array7Scrubbed = [1 => [1 => '&lt;script&gt;one&lt;/script&gt;',2 => '&lt;b&gt;two&lt;/b&gt;',3 => (object)["something" => '&lt;style&gt;other&lt;/style&gt;']], 2 => '&lt;h1&gt;two&lt;/h1&gt;'];

        $this->assertEquals($obj, XSSHelpers::scrubObjectOrArray($obj));
        $this->assertEquals($array, XSSHelpers::scrubObjectOrArray($array));
        $this->assertEquals($obj2, XSSHelpers::scrubObjectOrArray($obj2));
        $this->assertEquals($array2, XSSHelpers::scrubObjectOrArray($array2));
        $this->assertEquals($obj3, XSSHelpers::scrubObjectOrArray($obj3));
        $this->assertEquals($array3, XSSHelpers::scrubObjectOrArray($array3));
        $this->assertEquals($obj4, XSSHelpers::scrubObjectOrArray($obj4));
        $this->assertEquals($array4, XSSHelpers::scrubObjectOrArray($array4));
        $this->assertEquals($obj5Scrubbed, XSSHelpers::scrubObjectOrArray($obj5));
        $this->assertEquals($array5Scrubbed, XSSHelpers::scrubObjectOrArray($array5));
        $this->assertEquals($obj6Scrubbed, XSSHelpers::scrubObjectOrArray($obj6));
        $this->assertEquals($array6Scrubbed, XSSHelpers::scrubObjectOrArray($array6));
        $this->assertEquals($obj7Scrubbed, XSSHelpers::scrubObjectOrArray($obj7));
        $this->assertEquals($array7Scrubbed, XSSHelpers::scrubObjectOrArray($array7));
    }
}
