<?php
// $Rev: 179 $
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$dom = new simple_html_dom;

// -----------------------------------------------------------------------------
// test problem of last emelemt not found
$str = <<<HTML
<img src="src0"><p>foo</p><img src="src2">
HTML;

function callback_1($e) {
    if ($e->tag==='img')
        $e->outertext = '';
}

$dom->load($str);
$dom->set_callback('callback_1');
assert($dom=='<p>foo</p>');

// -----------------------------------------------
// innertext test
function callback_2($e) {
    if ($e->tag==='p')
        $e->innertext = 'bar';
}

$dom->load($str);
$dom->set_callback('callback_2');
assert($dom=='<img src="src0"><p>bar</p><img src="src2">');

// -----------------------------------------------
// attributes test
function callback_3($e) {
    if ($e->tag==='img')
        $e->src = 'foo';
}

$dom->load($str);
$dom->set_callback('callback_3');
assert($dom=='<img src="foo"><p>foo</p><img src="foo">');

function callback_4($e) {
    if ($e->tag==='img')
        $e->id = 'foo';
}

$dom->set_callback('callback_4');
assert($dom=='<img src="foo" id="foo"><p>foo</p><img src="foo" id="foo">');

// -----------------------------------------------
// attributes test2
//$dom = str_get_dom($str);
$dom->load($str);
$dom->remove_callback();
$dom->find('img', 0)->id = "foo";
assert($dom=='<img src="src0" id="foo"><p>foo</p><img src="src2">');

function callback_5($e) {
    if ($e->src==='src0')
        unset($e->id);
}

$dom->set_callback('callback_5');
assert($dom==$str);

// -----------------------------------------------------------------------------
// tear down
$dom->clear();
unset($dom);
?>