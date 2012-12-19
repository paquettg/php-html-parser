<?php
// $Rev$
error_reporting(E_ALL);
include_once('../simple_html_dom.php');

$start = microtime();
list($bu, $bs) = explode(' ', $start);
$html = file_get_html('slickspeed.htm');
list($eu, $es) = explode(' ', microtime());
echo sprintf('parse (%.1f)', ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000).'<br><br>';

assert(count($html->find('#title'))==1);
assert(count($html->find('div'))==51);
assert(count($html->find('div[class]'))==51);
assert(count($html->find('div.example'))==43);
assert(count($html->find('div[class=example]'))==43);
assert(count($html->find('.note'))==14);

assert(count($html->find('div[class^=exa]'))==43);
assert(count($html->find('div[class$=mple]'))==43);
assert(count($html->find('div[class*=e]'))==50);
assert(count($html->find('div[class!=made_up]'))==51);

assert(count($html->find('p'))==324);

echo 'All pass!<br>';
?>