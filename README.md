PHP Html Parser
==========================

[![Build Status](https://travis-ci.org/paquettg/php-html-parser.png)](https://travis-ci.org/paquettg/php-html-parser)
[![Coverage Status](https://coveralls.io/repos/paquettg/php-html-parser/badge.png)](https://coveralls.io/r/paquettg/php-html-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/paquettg/php-html-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/paquettg/php-html-parser/?branch=master)

PHPHtmlParser is a simple, flexible, html parser which allows you to select tags using any css selector, like jQuery. The goal is to assist in the development of tools which require a quick, easy way to scrap html, whether it's valid or not!

Install
-------

Install the latest version using composer.

```bash
$ composer require paquettg/php-html-parser
```

This package can be found on [packagist](https://packagist.org/packages/paquettg/php-html-parser) and is best loaded using [composer](http://getcomposer.org/). We support php 7.2, 7.3, and 7.4.

Basic Usage
-----

You can find many examples of how to use the DOM parser and any of its parts (which you will most likely never touch) in the tests directory. The tests are done using PHPUnit and are very small, a few lines each, and are a great place to start. Given that, I'll still be showing a few examples of how the package should be used. The following example is a very simplistic usage of the package.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a = $dom->find('a')[0];
echo $a->text; // "click here"
```

The above will output "click here". Simple, no? There are many ways to get the same result from the DOM, such as `$dom->getElementsbyTag('a')[0]` or `$dom->find('a', 0)`, which can all be found in the tests or in the code itself.

Support PHP Html Parser Financially
--------------

Get supported Monolog and help fund the project with the [Tidelift Subscription](https://tidelift.com/subscription/pkg/packagist-paquettg-php-html-parser?utm_source=packagist-paquettg-php-html-parser&utm_medium=referral&utm_campaign=enterprise).

Tidelift delivers commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use.

Loading Files
------------------

You may also seamlessly load a file into the DOM instead of a string, which is much more convenient and is how I expect most developers will be loading the HTML. The following example is taken from our test and uses the "big.html" file found there.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromFile('tests/data/big.html');
$contents = $dom->find('.content-border');
echo count($contents); // 10

foreach ($contents as $content)
{
	// get the class attr
	$class = $content->getAttribute('class');
	
	// do something with the html
	$html = $content->innerHtml;

	// or refine the find some more
	$child   = $content->firstChild();
	$sibling = $child->nextSibling();
}
```

This example loads the html from big.html, a real page found online, and gets all the content-border classes to process. It also shows a few things you can do with a node but it is not an exhaustive list of the methods that a node has available.

Loading URLs
----------------

Loading a URL is very similar to the way you would load the HTML from a file. 

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromUrl('http://google.com');
$html = $dom->outerHtml;

// or
$dom->loadFromUrl('http://google.com');
$html = $dom->outerHtml; // same result as the first example
```

loadFromUrl will, by default, use an implementation of the `\Psr\Http\Client\ClientInterface` to do the HTTP request and a default implementation of `\Psr\Http\Message\RequestInterface` to create the body of the request. You can easily implement your own version of either the client or request to use a custom HTTP connection when using loadFromUrl.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use App\Services\MyClient;

$dom = new Dom;
$dom->loadFromUrl('http://google.com', null, new MyClient());
$html = $dom->outerHtml;
```

As long as the client object implements the interface properly, it will use that object to get the content of the url.

Loading Strings
---------------

Loading a string directly is also easily done.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<html>String</html>');
$html = $dom->outerHtml;
```

Options
-------

You can also set parsing option that will effect the behavior of the parsing engine. You can set a global option array using the `setOptions` method in the `Dom` object or a instance specific option by adding it to the `load` method as an extra (optional) parameter.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

$dom = new Dom;
$dom->setOptions(
    // this is set as the global option level.
    (new Options())
        ->setStrict(true)
);

$dom->loadFromUrl('http://google.com', 
    (new Options())->setWhitespaceTextNode(false) // only applies to this load.
);

$dom->loadFromUrl('http://gmail.com'); // will not have whitespaceTextNode set to false.
```

At the moment we support 12 options.

**Strict**

Strict, by default false, will throw a `StrickException` if it find that the html is not strictly compliant (all tags must have a closing tag, no attribute with out a value, etc.).

**whitespaceTextNode**

The whitespaceTextNode, by default true, option tells the parser to save textnodes even if the content of the node is empty (only whitespace). Setting it to false will ignore all whitespace only text node found in the document.

**enforceEncoding**

The enforceEncoding, by default null, option will enforce an character set to be used for reading the content and returning the content in that encoding. Setting it to null will trigger an attempt to figure out the encoding from within the content of the string given instead. 

**cleanupInput**

Set this to `false` to skip the entire clean up phase of the parser. If this is set to true the next 3 options will be ignored. Defaults to `true`.

**removeScripts**

Set this to `false` to skip removing the script tags from the document body. This might have adverse effects. Defaults to `true`.

**removeStyles**

Set this to `false` to skip removing of style tags from the document body. This might have adverse effects. Defaults to `true`.

**preserveLineBreaks**

Preserves Line Breaks if set to `true`. If set to `false` line breaks are cleaned up as part of the input clean up process. Defaults to `false`.

**removeDoubleSpace**

Set this to `false` if you want to preserve whitespace inside of text nodes. It is set to `true` by default.

**removeSmartyScripts**

Set this to `false` if you want to preserve smarty script found in the html content. It is set to `true` by default.

**htmlSpecialCharsDecode**

By default this is set to `false`. Setting this to `true` will apply the php function `htmlspecialchars_decode` too all attribute values and text nodes.

**selfClosing**

This option contains an array of all self closing tags. These tags must be self closing and the parser will force them to be so if you have strict turned on. You can update this list with any additional tags that can be used as a self closing tag when using strict. You can also remove tags from this array or clear it out completly.

**noSlash**

This option contains an array of all tags that can not be self closing. The list starts off as empty but you can add elements as you wish.

Static Facade
-------------

You can also mount a static facade for the Dom object.

```PHP
PHPHtmlParser\StaticDom::mount();

Dom::loadFromFile('tests/big.html');
$objects = Dom::find('.content-border');

```

The above php block does the same find and load as the first example but it is done using the static facade, which supports all public methods found in the Dom object.

Modifying The Dom
-----------------

You can always modify the dom that was created from any loading method. To change the attribute of any node you can just call the `setAttribute` method.

```php
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a = $dom->find('a')[0];
$a->setAttribute('class', 'foo');
echo $a->getAttribute('class'); // "foo"
```

You may also get the `PHPHtmlParser\Dom\Tag` class directly and manipulate it as you see fit.

```php
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
/** @var Dom\Node\AbstractNode $a */
$a   = $dom->find('a')[0];
$tag = $a->getTag();
$tag->setAttribute('class', 'foo');
echo $a->getAttribute('class'); // "foo"
```

It is also possible to remove a node from the tree. Simply call the `delete` method on any node to remove it from the tree. It is important to note that you should unset the node after removing it from the `DOM``, it will still take memory as long as it is not unset.

```php
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
/** @var Dom\Node\AbstractNode $a */
$a   = $dom->find('a')[0];
$a->delete();
unset($a);
echo $dom; // '<div class="all"><p>Hey bro, <br /> :)</p></div>');
```

You can modify the text of `TextNode` objects easily. Please note that, if you set an encoding, the new text will be encoded using the existing encoding.

```php
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
/** @var Dom\Node\InnerNode $a */
$a   = $dom->find('a')[0];
$a->firstChild()->setText('biz baz');
echo $dom; // '<div class="all"><p>Hey bro, <a href="google.com">biz baz</a><br /> :)</p></div>'
```
