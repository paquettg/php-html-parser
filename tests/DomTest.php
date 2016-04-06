<?php

use PHPHtmlParser\Dom;

class DomTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        Mockery::close();
    }

    public function testLoad()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
    }

    /**
     * @expectedException PHPHtmlParser\Exceptions\NotLoadedException
     */
    public function testNotLoaded()
    {
        $dom = new Dom;
        $div = $dom->find('div', 0);
    }

    public function testIncorrectAccess()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals(null, $div->foo);
    }

    public function testLoadSelfclosingAttr()
    {
        $dom = new Dom;
        $dom->load("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', $br->outerHtml);
    }

    public function testLoadSelfclosingAttrToString()
    {
        $dom = new Dom;
        $dom->load("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', (string) $br);
    }

    public function testLoadEscapeQuotes()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $div->outerHtml);
    }

    public function testLoadNoOpeningTag()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><font color="red"><strong>PR Manager</strong></font></b><div class="content">content</div></div>');
        $this->assertEquals('content', $dom->find('.content', 0)->text);
    }

    public function testLoadNoClosingTag()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $root = $dom->find('div', 0)->getParent();
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div><br />', $root->outerHtml);
    }

    public function testLoadAttributeOnSelfClosing()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br class="both" />');
        $br = $dom->find('br', 0);
        $this->assertEquals('both', $br->getAttribute('class'));
    }

    public function testLoadClosingTagOnSelfClosing()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagAddSelfClosingTag()
    {
        $dom = new Dom;
        $dom->addSelfClosingTag('mytag');
        $dom->load('<div class="all"><mytag><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></mytag></div>');
        $this->assertEquals('<mytag /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagAddSelfClosingTagArray()
    {
        $dom = new Dom;
        $dom->addSelfClosingTag([
            'mytag',
            'othertag'
        ]);
        $dom->load('<div class="all"><mytag><p>Hey bro, <a href="google.com" data-quote="\"">click here</a><othertag></div>');
        $this->assertEquals('<mytag /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a><othertag /></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagRemoveSelfClosingTag()
    {
        $dom = new Dom;
        $dom->removeSelfClosingTag('br');
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></br>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagClearSelfClosingTag()
    {
        $dom = new Dom;
        $dom->clearSelfClosingTags();
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></br>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadNoValueAttribute()
    {
        $dom = new Dom;
        $dom->load('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>');
        $this->assertEquals('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadNoValueAttributeBefore()
    {
        $dom = new Dom;
        $dom->load('<div class="content"><div ui-view class="grid-container">Main content here</div></div>');
        $this->assertEquals('<div class="content"><div ui-view class="grid-container">Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadUpperCase()
    {
        $dom = new Dom;
        $dom->load('<DIV CLASS="ALL"><BR><P>hEY BRO, <A HREF="GOOGLE.COM" DATA-QUOTE="\"">CLICK HERE</A></BR></DIV>');
        $this->assertEquals('<br /><p>hEY BRO, <a href="GOOGLE.COM" data-quote="\"">CLICK HERE</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadWithFile()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFile()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFileFind()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testLoadUtf8()
    {
        $dom = new Dom;
        $dom->load('<p>Dzień</p>');
        $this->assertEquals('Dzień', $dom->find('p', 0)->text);
    }

    public function testLoadFileBig()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/big.html');
        $this->assertEquals(10, count($dom->find('.content-border')));
    }

    public function testLoadFileBigTwice()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/big.html');
        $post = $dom->find('.post-row', 0);
        $this->assertEquals(' <p>Журчанье воды<br /> Черно-белые тени<br /> Вновь на фонтане</p> ', $post->find('.post-message', 0)->innerHtml);
    }

    public function testLoadFileBigTwicePreserveOption()
    {
        $dom = new Dom;
        $dom->loadFromFile('tests/files/big.html', ['preserveLineBreaks' => true]);
        $post = $dom->find('.post-row', 0);
        $this->assertEquals('<p>Журчанье воды<br />
Черно-белые тени<br />
Вновь на фонтане</p>', trim($post->find('.post-message', 0)->innerHtml));
    }

    public function testLoadFromUrl()
    {
        $curl = Mockery::mock('PHPHtmlParser\CurlInterface');
        $curl->shouldReceive('get')
             ->once()
             ->with('http://google.com')
             ->andReturn(file_get_contents('tests/files/small.html'));
        
        $dom = new Dom;
        $dom->loadFromUrl('http://google.com', [], $curl);
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testToStringMagic()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', (string) $dom);
    }

    public function testGetMagic()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $dom->innerHtml);
    }

    public function testFirstChild()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $dom->firstChild()->outerHtml);
    }

    public function testLastChild()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $this->assertEquals('<br />', $dom->lastChild()->outerHtml);
    }

    public function testGetElementById()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<a href="google.com" id="78">click here</a>', $dom->getElementById('78')->outerHtml);
    }

    public function testGetElementsByTag()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByTag('p')[0]->outerHtml);
    }

    public function testGetElementsByClass()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByClass('all')[0]->innerHtml);
    }

    public function testEnforceEncoding()
    {
        $dom = new Dom;
        $dom->load('tests/files/horrible.html', [
            'enforceEncoding' => 'UTF-8',
        ]);
        $this->assertNotEquals('<input type="submit" tabindex="0" name="submit" value="Информации" />', $dom->find('table input', 1)->outerHtml);
    }

    public function testScriptCleanerScriptTag()
    {
        $dom = new Dom;
        $dom->load('
        <p>.....</p>
        <script>
        Some code ... 
        document.write("<script src=\'some script\'><\/script>") 
        Some code ... 
        </script>
        <p>....</p>');
        $this->assertEquals('....', $dom->getElementsByTag('p')[1]->innerHtml);
    }

    public function testMultipleDoubleQuotes()
    {
        $dom = new Dom;
        $dom->load('<a title="This is a "test" of double quotes" href="http://www.example.com">Hello</a>');
        $this->assertEquals('This is a "test" of double quotes', $dom->getElementsByTag('a')[0]->title);
    }

    public function testMultipleSingleQuotes()
    {
        $dom = new Dom;
        $dom->load("<a title='Ain't this the best' href=\"http://www.example.com\">Hello</a>");
        $this->assertEquals("Ain't this the best", $dom->getElementsByTag('a')[0]->title);
    }

    public function testBeforeClosingTag()
    {
        $dom = new Dom;
        $dom->load("<div class=\"stream-container \"  > <div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\">");
        $this->assertEquals("<div class=\"stream-container \"> <div class=\"stream-item js-new-items-bar-container\"> </div> <div class=\"stream\"></div></div>", (string) $dom);
    }

    public function testCodeTag()
    {
        $dom = new Dom;
        $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertEquals('<strong>hello</strong><code class="language-php">$foo = "bar";</code>', (string) $dom);
    }

    public function testDeleteNode()
    {
        $dom = new Dom;
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $a   = $dom->find('a')[0];
        $a->delete();
        unset($a);
        $this->assertEquals('<div class="all"><p>Hey bro, <br /> :)</p></div>', (string) $dom);
    }
}
