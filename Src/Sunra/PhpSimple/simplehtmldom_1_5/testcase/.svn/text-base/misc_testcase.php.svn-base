<?php
// $Rev$
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$dom = new simple_html_dom;


// -----------------------------------------------------------------------------
// test problem of last emelemt not found
$str = <<<HTML
<img class="class0" id="id0" src="src0">
<img class="class1" id="id1" src="src1">
<img class="class2" id="id2" src="src2">
HTML;

$dom->load($str);
$es = $dom->find('img');
assert(count($es)==3);
assert($es[0]->src=='src0');
assert($es[1]->src=='src1');
assert($es[2]->src=='src2');
assert($es[0]->innertext=='');
assert($es[1]->innertext=='');
assert($es[2]->innertext=='');
assert($es[0]->outertext=='<img class="class0" id="id0" src="src0">');
assert($es[1]->outertext=='<img class="class1" id="id1" src="src1">');
assert($es[2]->outertext=='<img class="class2" id="id2" src="src2">');
assert($dom->find('img', 0)->src=='src0');
assert($dom->find('img', 1)->src=='src1');
assert($dom->find('img', 2)->src=='src2');
assert($dom->find('img', 3)===null);
assert($dom->find('img', 99)===null);
assert($dom->save()==$str);

// -----------------------------------------------------------------------------
// test error tag
$str = <<<HTML
<img class="class0" id="id0" src="src0"><p>p1</p>
<img class="class1" id="id1" src="src1"><p>
<img class="class2" id="id2" src="src2"></a></div>
HTML;

$dom = str_get_html($str);
$es = $dom->find('img');
assert(count($es)==3);
assert($es[0]->src=='src0');
assert($es[1]->src=='src1');
assert($es[2]->src=='src2');

$es = $dom->find('p');
assert($es[0]->innertext=='p1');
assert($dom==$str);

// -----------------------------------------------------------------------------
// tear down
$dom->clear();
unset($dom);
?>