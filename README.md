PHP Html Parser
==========================

Version 1.7.0

[![Build Status](https://travis-ci.org/paquettg/php-html-parser.png)](https://travis-ci.org/paquettg/php-html-parser)
[![Coverage Status](https://coveralls.io/repos/paquettg/php-html-parser/badge.png)](https://coveralls.io/r/paquettg/php-html-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/paquettg/php-html-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/paquettg/php-html-parser/?branch=master)

PHPHtmlParser is a simple, flexible, html parser which allows you to select tags using any css selector, like jQuery. The goal is to assiste in the development of tools which require a quick, easy way to scrap html, whether it's valid or not! This project was original supported by [sunra/php-simple-html-dom-parser](https://github.com/sunra/php-simple-html-dom-parser) but the support seems to have stopped so this project is my adaptation of his previous work.

Install
-------

This package can be found on [packagist](https://packagist.org/packages/paquettg/php-html-parser) and is best loaded using [composer](http://getcomposer.org/). We support php 5.4, 5.5, and hhvm 2.3.

Usage
-----

You can find many examples of how to use the dom parser and any of its parts (which you will most likely never touch) in the tests directory. The tests are done using PHPUnit and are very small, a few lines each, and are a great place to start. Given that, I'll still be showing a few examples of how the package should be used. The following example is a very simplistic usage of the package.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a = $dom->find('a')[0];
echo $a->text; // "click here"
```

The above will output "click here". Simple no? There are many ways to get the same result from the dome, such as `$dom->getElementsbyTag('a')[0]` or `$dom->find('a', 0)` which can all be found in the tests or in the code itself.

Loading Files
------------------

You may also seamlessly load a file into the dom instead of a string, which is much more convinient and is how I except most developers will be loading the html. The following example is taken from our test and uses the "big.html" file found there.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromFile('tests/big.html');
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

This example loads the html from big.html, a real page found online, and gets all the content-border classes to process. It also shows a few things you can do with a node but it is not an exhaustive list of methods that a node has avaiable.

Alternativly, you can always use the `load()` method to load the file. It will attempt to find the file using `file_exists` and, if succesfull, will call `loadFromFile()` for you. The same applies to a URL and `loadFromUrl()` method.

Loading Url
----------------

Loading a url is very similar to the way you would load the html from a file. 

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadFromUrl('http://google.com');
$html = $dom->outerHtml;

// or
$dom->load('http://google.com');
$html = $dom->outerHtml; // same result as the first example
```

What makes the loadFromUrl method note worthy is the `PHPHtmlParser\CurlInterface` parameter, an optional second parameter. By default, we use the `PHPHtmlParser\Curl` class to get the contents of the url. On the other hand, though, you can inject your own implementation of CurlInterface and we will attempt to load the url using what ever tool/settings you want, up to you.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use App\Services\Connector;

$dom = new Dom;
$dom->loadFromUrl('http://google.com', [], new Connector);
$html = $dom->outerHtml;
```

As long as the Connector object implements the `PHPHtmlParser\CurlInterface` interface properly it will use that object to get the content of the url instead of the default `PHPHtmlParser\Curl` class.

Loading Strings
---------------

Loading a string directly, with out the checks in `load()` is also easely done.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->loadStr('<html>String</html>', []);
$html = $dom->outerHtml;
```

If the string is to long, depending on your file system, the `load()` method will throw a warning. If this happens you can just call the above method to bypass the `is_file()` check in the `load()` method.

Options
-------

You can also set parsing option that will effect the behavior of the parsing engine. You can set a global option array using the `setOptions` method in the `Dom` object or a instance specific option by adding it to the `load` method as an extra (optional) parameter.

```php
// Assuming you installed from Composer:
require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$dom = new Dom;
$dom->setOptions([
	'strict' => true, // Set a global option to enable strict html parsing.
]);

$dom->load('http://google.com', [
	'whitespaceTextNode' => false, // Only applies to this load.
]);

$dom->load('http://gmail.com'); // will not have whitespaceTextNode set to false.
```

At the moment we support 7 options.

**Strict**

Strict, by default false, will throw a `StrickException` if it find that the html is not strict complient (all tags must have a clossing tag, no attribute with out a value, etc.).

**whitespaceTextNode**

The whitespaceTextNode, by default true, option tells the parser to save textnodes even if the content of the node is empty (only whitespace). Setting it to false will ignore all whitespace only text node found in the document.

**enforceEncoding**

The enforceEncoding, by default null, option will enforce an charater set to be used for reading the content and returning the content in that encoding. Setting it to null will trigger an attempt to figure out the encoding from within the content of the string given instead. 

**cleanupInput**

Set this to `true` to skip the entire clean up phase of the parser. If this is set to true the next 3 options will be ignored. Defaults to `false`.

**removeScripts**

Set this to `false` to skip removing the script tags from the document body. This might have adverse effects. Defaults to `true`.

**removeStyles**

Set this to `false` to skip removing of style tags from the document body. This might have adverse effects. Defaults to `true`.

**preserveLineBreaks**

Preserves Line Breaks if set to `true`. If set to `false` line breaks are cleaned up as part of the input clean up process. Defaults to `false`.

Static Facade
-------------

You can also mount a static facade for the Dom object.

```PHP
PHPHtmlParser\StaticDom::mount();

Dom::load('tests/big.hmtl');
$objects = Dom::find('.content-border');

```

The above php block does the same find and load as the first example but it is done using the static facade, which supports all public methods found in the Dom object.

Modifying The Dom
-----------------

You can always modify the dom that was created from any loading method. To change the attribute of any node you can just call the `setAttribute` method.

```php
$dom = new Dom;
$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a = $dom->find('a')[0];
$a->setAttribute('class', 'foo');
echo $a->getAttribute('class'); // "foo"
```

You may also get the `PHPHtmlParser\Dom\Tag` class directly and manipulate it as you see fit.

```php
$dom = new Dom;
$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a   = $dom->find('a')[0];
$tag = $a->getTag();
$tag->setAttribute('class', 'foo');
echo $a->getAttribute('class'); // "foo"
```

It is also possible to remove a node from the tree. Simply call the `delete` method on any node to remove it from the tree. It is important to note that you should unset the node after removing it from the `DOM``, it will still take memory as long as it is not unset.

```php
$dom = new Dom;
$dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
$a   = $dom->find('a')[0];
$a->delete();
unset($a);
echo $dom; // '<div class="all"><p>Hey bro, <br /> :)</p></div>');
```
