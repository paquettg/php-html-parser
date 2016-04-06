### Development

## 1.7.0

- Added .scrutinizer.yml to repo
- Reformated code to PSR-1/2
- Improved the test coverage and some small code changes
- Added removeAttribute and removeAllAttributes tag methods fixes #57
- Added replaceNode method implements #52
- Added a delete method. fixes #43
- Added semicolon after &#10 for linebreak preservation. fixes #62
- Removed code that removed <code> tag fixes #60
- Added new test related to #63
- Refactored the nodes into inner and leaf nodes
- Fixed Strings example in README
- Close this header so the markdown will render properly
- Added preserve line break option. Defaults to false.


## 1.6.9

- Added Changelog
- Fixed issue with spaces befor closing tag Fixes #45
- Fixed some code quality issues found by scrutinizer
- Added Scrutinizer to README
- Reformated code to comply with PSR-1/2
- Added preserve line break option. Defaults to false. fixes #40
- Updated the tests
- Added options: cleanupInput, removeScripts and removeStyles

## 1.6.8

- Added comments and reformated some code
- Added support for non-escaped quotes in attribute value fixes #37
- Cleaned up the comments and php docs
- Removed version in composer json
- Updated composer version
- Refactoring out isChild method.
- Updated in code documentation
- Updated composer

$$ 1.6.7

- Added tests for the new array access
- Added feature to allow array usage of html node. fixes #26
- Update HtmlNode.php
- Added test to cover issue #28
- FIX: File name is longer than the maximum allowed path

## 1.6.6

- Replaced preg_replace with mb_ereg_replace
- Added child selector fixes #24
- Updated the dev version of phpunit

## 1.6.5

- Fixed bug when no attribute tags are last tag (with out space). fixes #16
- Fixed some documentation inconsistencies fixes #15
- Made loadStr a public methor Fixes #18
- Update a problem with the README fixes #11
- Added setAttribute to the node fixes #7
- Check if open_basedir is enabled: Dont use CURLOPT_FOLLOWLOCATION

## 1.6.4

- Added tests and updated README
- Updated the tests and moved some files
- Added the option to enforce the encoding
- Fixed a problem with handeling the unknown child exception
- Updated some tests
- Added coverall badge and package

## 1.6.3

- Added initial support for 'strict' parsing option
- Added an optional paramter to enable recursive text
- Added appropriat Options tests
- Changed all exception to specific objects
- Added a whitespaceTextNode option and test
- Added support for an options array

## 1.6.2

- Standardised indentation for easyer reading on github
- Update AbstractNode.php
- Added a test for hhvm in my travis.yml
- Added a LICENSE.md file for MIT
- Added build status to README
- Added travis.yml
- Changed the file name of the abstract node
- fixed code in collection class where instance of arrayIterator is to be rturned
- Updated documentation
- Added a curl interface and a simple curl class.
- Removed the Guzzle dependancy
- Abstracted the Node class as it should have been done in the first place
- Added integrity checks for the cached html
- Added some basic caching of the dom html
- Added a toArray() method to the collection and a test

## 1.6.1

- Moved back to using guzzle so expections are thrown when their was an error with loading a url
- Added tests for the Static Facade Fixed a few issues brought to light from the new tests
- Added a static facade
- Changed encoding to be a local attribute instead of a static attribute
- Solved issue #2 When you attempt to load an html page from a URL using loadFromUrl the encoding is incorrect.
- Added easyer loading of files and urls. Still have a problem with encoding while loading from url.
- Added guzzle and loadFromUrl option
- Fixed an issue with no value attributes
- Added magic and each methods to the collection. Plus some tests
- Added a collection object
- Added charset encoding
- fixed a bug with closing tags If a closing tag did not have an opening tag it would cause the scan to end instead of ignoring the closing tag.
