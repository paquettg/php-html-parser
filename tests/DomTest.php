<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use PHPUnit\Framework\TestCase;

class DomTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * <![CDATA[ should not be modified when cleanupInput is set to false.
     */
    public function testParsingCData()
    {
        $html = "<script type=\"text/javascript\">/* <![CDATA[ */var et_core_api_spam_recaptcha = '';/* ]]> */</script>";
        $dom = new Dom();
        $dom->setOptions((new Options())->setCleanupInput(false));
        $dom->loadStr($html);
        $this->assertSame($html, $dom->root->outerHtml());
    }

    public function testLoadSelfclosingAttr()
    {
        $dom = new Dom();
        $dom->loadStr("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', $br->outerHtml);
    }

    public function testLoadSelfclosingAttrToString()
    {
        $dom = new Dom();
        $dom->loadStr("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', (string) $br);
    }

    public function testLoadNoOpeningTag()
    {
        $dom = new Dom();
        $dom->loadStr('<div class="all"><font color="red"><strong>PR Manager</strong></font></b><div class="content">content</div></div>');
        $this->assertEquals('content', $dom->find('.content', 0)->text);
    }

    public function testLoadNoValueAttribute()
    {
        $dom = new Dom();
        $dom->loadStr('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>');
        $this->assertEquals('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadBackslashAttributeValue()
    {
        $dom = new Dom();
        $dom->loadStr('<div class="content"><div id="\" class="grid-container" ui-view>Main content here</div></div>');
        $this->assertEquals('<div class="content"><div id="\" class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadNoValueAttributeBefore()
    {
        $dom = new Dom();
        $dom->loadStr('<div class="content"><div ui-view class="grid-container">Main content here</div></div>');
        $this->assertEquals('<div class="content"><div ui-view class="grid-container">Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadUpperCase()
    {
        $dom = new Dom();
        $dom->loadStr('<DIV CLASS="ALL"><BR><P>hEY BRO, <A HREF="GOOGLE.COM" DATA-QUOTE="\"">CLICK HERE</A></BR></DIV>');
        $this->assertEquals('<br /><p>hEY BRO, <a href="GOOGLE.COM" data-quote="\"">CLICK HERE</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadWithFile()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFile()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFileFind()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testLoadFromFileNotFound()
    {
        $dom = new Dom();
        $this->expectException(\PHPHtmlParser\Exceptions\LogicalException::class);
        $dom->loadFromFile('tests/data/files/unkowne.html');
    }

    public function testLoadUtf8()
    {
        $dom = new Dom();
        $dom->loadStr('<p>Dzień</p>');
        $this->assertEquals('Dzień', $dom->find('p', 0)->text);
    }

    public function testLoadFileWhitespace()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setCleanupInput(false));
        $dom->loadFromFile('tests/data/files/whitespace.html');
        $this->assertEquals(1, \count($dom->find('.class')));
        $this->assertEquals('<span><span class="class"></span></span>', (string) $dom);
    }

    public function testLoadFileBig()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(20, \count($dom->find('.content-border')));
    }

    public function testLoadFileBigTwice()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/big.html');
        $post = $dom->find('.post-row', 0);
        $this->assertEquals(' <p>Журчанье воды<br /> Черно-белые тени<br /> Вновь на фонтане</p> ', $post->find('.post-message', 0)->innerHtml);
    }

    public function testLoadFileBigTwicePreserveOption()
    {
        $dom = new Dom();
        $dom->loadFromFile(
            'tests/data/files/big.html',
            (new Options())->setPreserveLineBreaks(true)
        );
        $post = $dom->find('.post-row', 0);
        $this->assertEquals(
            "<p>Журчанье воды<br />\nЧерно-белые тени<br />\nВновь на фонтане</p>",
            \trim($post->find('.post-message', 0)->innerHtml)
        );
    }

    public function testLoadFromUrl()
    {
        $streamMock = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
        $streamMock->shouldReceive('getContents')
            ->once()
            ->andReturn(\file_get_contents('tests/data/files/small.html'));
        $responseMock = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
        $responseMock->shouldReceive('getBody')
            ->once()
            ->andReturn($streamMock);
        $clientMock = Mockery::mock(\Psr\Http\Client\ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->once()
            ->andReturn($responseMock);

        $dom = new Dom();
        $dom->loadFromUrl('http://google.com', null, $clientMock);
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testScriptCleanerScriptTag()
    {
        $dom = new Dom();
        $dom->loadStr('
        <p>.....</p>
        <script>
        Some code ...
        document.write("<script src=\'some script\'><\/script>")
        Some code ...
        </script>
        <p>....</p>');
        $this->assertEquals('....', $dom->getElementsByTag('p')[1]->innerHtml);
    }

    public function testClosingSpan()
    {
        $dom = new Dom();
        $dom->loadStr("<div class='foo'></span>sometext</div>");
        $this->assertEquals('sometext', $dom->getElementsByTag('div')[0]->innerHtml);
    }

    public function testMultipleDoubleQuotes()
    {
        $dom = new Dom();
        $dom->loadStr('<a title="This is a "test" of double quotes" href="http://www.example.com">Hello</a>');
        $this->assertEquals('This is a "test" of double quotes', $dom->getElementsByTag('a')[0]->title);
    }

    public function testMultipleSingleQuotes()
    {
        $dom = new Dom();
        $dom->loadStr("<a title='Ain't this the best' href=\"http://www.example.com\">Hello</a>");
        $this->assertEquals("Ain't this the best", $dom->getElementsByTag('a')[0]->title);
    }

    public function testBeforeClosingTag()
    {
        $dom = new Dom();
        $dom->loadStr('<div class="stream-container "  > <div class="stream-item js-new-items-bar-container"> </div> <div class="stream">');
        $this->assertEquals('<div class="stream-container "> <div class="stream-item js-new-items-bar-container"> </div> <div class="stream"></div></div>', (string) $dom);
    }

    public function testCodeTag()
    {
        $dom = new Dom();
        $dom->loadStr('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertEquals('<strong>hello</strong><code class="language-php">$foo = "bar";</code>', (string) $dom);
    }

    public function testCountChildren()
    {
        $dom = new Dom();
        $dom->loadStr('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertEquals(2, $dom->countChildren());
    }

    public function testGetChildrenArray()
    {
        $dom = new Dom();
        $dom->loadStr('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertIsArray($dom->getChildren());
    }

    public function testHasChildren()
    {
        $dom = new Dom();
        $dom->loadStr('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertTrue($dom->hasChildren());
    }

    public function testWhitespaceInText()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveDoubleSpace(false));
        $dom->loadStr('<pre>    Hello world</pre>');
        $this->assertEquals('<pre>    Hello world</pre>', (string) $dom);
    }

    public function testGetComplexAttribute()
    {
        $dom = new Dom();
        $dom->loadStr('<a href="?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4" class="pagination-next">Next <span class="chevron">&gt;</span></a>');
        $href = $dom->find('a', 0)->href;
        $this->assertEquals('?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4', $href);
    }

    public function testGetComplexAttributeHtmlSpecialCharsDecode()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setHtmlSpecialCharsDecode(true));
        $dom->loadStr('<a href="?search=Fort+William&amp;session_type=face&amp;distance=100&amp;uqs=119846&amp;page=4" class="pagination-next">Next <span class="chevron">&gt;</span></a>');
        $a = $dom->find('a', 0);
        $this->assertEquals('Next <span class="chevron">></span>', $a->innerHtml);
        $href = $a->href;
        $this->assertEquals('?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4', $href);
    }

    public function testGetChildrenNoChildren()
    {
        $dom = new Dom();
        $dom->loadStr('<div>Test <img src="test.jpg"></div>');

        $imgNode = $dom->root->find('img');
        $children = $imgNode->getChildren();
        $this->assertTrue(\count($children) === 0);
    }

    public function testInfiniteLoopNotHappening()
    {
        $dom = new Dom();
        $dom->loadStr('<html>
                <head>
                <meta http-equiv="refresh" content="5; URL=http://www.example.com">
                <meta http-equiv="cache-control" content="no-cache">
                <meta http-equiv="pragma" content="no-cache">
                <meta http-equiv="expires" content="0">
                </head>
                <');

        $metaNodes = $dom->root->find('meta');
        $this->assertEquals(4, \count($metaNodes));
    }

    public function testFindOrder()
    {
        $str = '<p><img src="http://example.com/first.jpg"></p><img src="http://example.com/second.jpg">';
        $dom = new Dom();
        $dom->loadStr($str);
        $images = $dom->find('img');

        $this->assertEquals('<img src="http://example.com/first.jpg" />', (string) $images[0]);
    }

    public function testCaseInSensitivity()
    {
        $str = "<FooBar Attribute='asdf'>blah</FooBar>";
        $dom = new Dom();
        $dom->loadStr($str);

        $FooBar = $dom->find('FooBar');
        $this->assertEquals('asdf', $FooBar->getAttribute('attribute'));
    }

    public function testCaseSensitivity()
    {
        $str = "<FooBar Attribute='asdf'>blah</FooBar>";
        $dom = new Dom();
        $dom->loadStr($str);

        $FooBar = $dom->find('FooBar');
        $this->assertEquals('asdf', $FooBar->Attribute);
    }

    public function testEmptyAttribute()
    {
        $str = '<ul class="summary"><li class></li>blah<li class="foo">what</li></ul>';
        $dom = new Dom();
        $dom->loadStr($str);

        $items = $dom->find('.summary .foo');
        $this->assertEquals(1, \count($items));
    }

    public function testInnerText()
    {
        $html = <<<EOF
<body class="" style="" data-gr-c-s-loaded="true">123<a>456789</a><span>101112</span></body>
EOF;
        $dom = new Dom();
        $dom->loadStr($html);
        $this->assertEquals($dom->innerText, '123456789101112');
    }

    public function testMultipleSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[type=text][name=foo][baz=fig]');
        $this->assertEquals(1, \count($items));
    }

    public function testNotSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[type!=foo]');
        $this->assertEquals(1, \count($items));
    }

    public function testStartSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[name^=f]');
        $this->assertEquals(1, \count($items));
    }

    public function testEndSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz$=g]');
        $this->assertEquals(1, \count($items));
    }

    public function testStarSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz*=*]');
        $this->assertEquals(1, \count($items));
    }

    public function testStarFullRegexSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz*=/\w+/]');
        $this->assertEquals(1, \count($items));
    }

    public function testFailedSquareSelector()
    {
        $dom = new Dom();
        $dom->loadStr('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz%=g]');
        $this->assertEquals(1, \count($items));
    }

    public function testLoadGetAttributeWithBackslash()
    {
        $dom = new Dom();
        $dom->loadStr('<div><a href="/test/"><img alt="\" src="/img/test.png" /><br /></a><a href="/demo/"><img alt="demo" src="/img/demo.png" /></a></div>');
        $imgs = $dom->find('img', 0);
        $this->assertEquals('/img/test.png', $imgs->getAttribute('src'));
    }

    public function test25ChildrenFound()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setWhitespaceTextNode(false));
        $dom->loadFromFile('tests/data/files/51children.html');
        $children = $dom->find('#red-line-g *');
        $this->assertEquals(25, \count($children));
    }

    public function testHtml5PageloadStr()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/html5.html');

        /** @var Node\AbstractNode $meta */
        $div = $dom->find('div.d-inline-block', 0);
        $this->assertEquals('max-width: 29px', $div->getAttribute('style'));
    }

    public function testFindAttributeInBothParentAndChild()
    {
        $dom = new Dom();
        $dom->loadStr('<parent attribute="something">
    <child attribute="anything"></child>
</parent>');

        /** @var Node\AbstractNode $meta */
        $nodes = $dom->find('[attribute]');
        $this->assertCount(2, $nodes);
    }

    public function testLessThanCharacterInJavascript()
    {
        $results = (new Dom())->loadStr(
            '<html><head><script type="text/javascript">
            console.log(1 < 3);
        </script></head><body><div id="panel"></div></body></html>',
            (new Options())->setCleanupInput(false)
                ->setRemoveScripts(false)
        )->find('body');
        $this->assertCount(1, $results);
    }

    public function testUniqueIdForAllObjects()
    {
        // Create a dom which will be used as a parent/container for a paragraph
        $dom1 = new \PHPHtmlParser\Dom();
        $dom1->loadStr('<div>A container div</div>'); // Resets the counter (doesn't matter here as the counter was 0 even without resetting)
        $div = $dom1->firstChild();

        // Create a paragraph outside of the first dom
        $dom2 = new \PHPHtmlParser\Dom();
        $dom2->loadStr('<p>Our new paragraph.</p>'); // Resets the counter
        $paragraph = $dom2->firstChild();

        $div->addChild($paragraph);

        $this->assertEquals('A container div<p>Our new paragraph.</p>', $div->innerhtml);
    }

    public function testFindDescendantsOfMatch()
    {
        $dom = new Dom();
        $dom->loadStr('<p>
        <b>
            test
            <b>testing</b>
            <b>This is a test</b>
            <i>italic</i>
            <b>password123</b>
        </b>
        <i><b>another</b></i>
    </p>');

        $nodes = $dom->find('b');
        $this->assertCount(5, $nodes);
    }

    public function testCompatibleWithWordPressShortcode()
    {
        $dom = new Dom();
        $dom->loadStr('<p>
[wprs_alert type="success" content="this is a short code" /]
</p>');

        $node = $dom->find('p', 0);
        $this->assertEquals(' [wprs_alert type="success" content="this is a short code" /] ', $node->innerHtml);
    }

    public function testBrokenHtml()
    {
        $dom = new Dom();
        $dom->loadStr('<the thing broke itV');

        $this->assertEquals('<the thing broke itv></the>', $dom->outerHtml);
    }

    public function testXMLOpeningToken()
    {
        $dom = new Dom();
        $dom->loadStr('<?xml version="1.0" encoding="UTF-8"?><p>fun time</p>');

        $this->assertEquals('<?xml version="1.0" encoding="UTF-8" ?><p>fun time</p>', $dom->outerHtml);
    }

    /**
     * Test to cover issue found in ticket #221.
     */
    public function testRandomTagInMiddleOfText()
    {
        $dom = new Dom();
        $dom->loadStr('<p>Hello, this is just a test in which <55 names with some other text > should be interpreted as text</p>');

        $this->assertEquals('<p>Hello, this is just a test in which <55 names with some other text> should be interpreted as text</55></p>', $dom->outerHtml);
    }

    public function testHttpCall()
    {
        // Apparently google.com uses utf-8 as the encoding, but the default for Dom is case sensitive encoding.
        // @todo this should be resolved by the package owner

        $this->expectException(\StringEncoder\Exceptions\InvalidEncodingException::class);
        $dom = new Dom();
        $dom->loadFromUrl('http://google.com');
        $this->assertNotEmpty($dom->outerHtml);
    }
}
