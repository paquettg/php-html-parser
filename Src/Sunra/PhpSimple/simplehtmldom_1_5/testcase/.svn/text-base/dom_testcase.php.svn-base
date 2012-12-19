<?php
// $Rev$
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$html = new simple_html_dom;

// -----------------------------------------------------------------------------
// DOM tree test
$html->load('');
$e = $html->root;
assert($e->first_child()==null);
assert($e->last_child()==null);
assert($e->next_sibling()==null);
assert($e->prev_sibling()==null);
// -----------------------------------------------
$str = '<div id="div1"></div>';
$html->load($str);

$e = $html->root;
assert($e->first_child()->id=='div1');
assert($e->last_child()->id=='div1');
assert($e->next_sibling()==null);
assert($e->prev_sibling()==null);
assert($e->plaintext=='');
assert($e->innertext=='<div id="div1"></div>');
assert($e->outertext==$str);
// -----------------------------------------------
$str = <<<HTML
<div id="div1">
    <div id="div10"></div>
    <div id="div11"></div>
    <div id="div12"></div>
</div>
HTML;
$html->load($str);
assert($html==$str);

$e = $html->find('div#div1', 0);
assert(isset($e->id)==true);
assert(isset($e->_not_exist)==false);
assert($e->first_child()->id=='div10');
assert($e->last_child()->id=='div12');
assert($e->next_sibling()==null);
assert($e->prev_sibling()==null);
// -----------------------------------------------
$str = <<<HTML
<div id="div0">
    <div id="div00"></div>
</div>
<div id="div1">
    <div id="div10"></div>
    <div id="div11"></div>
    <div id="div12"></div>
</div>
<div id="div2"></div>
HTML;
$html->load($str);
assert($html==$str);

$e = $html->find('div#div1', 0);
assert($e->first_child()->id=='div10');
assert($e->last_child()->id=='div12');
assert($e->next_sibling()->id=='div2');
assert($e->prev_sibling()->id=='div0');

$e = $html->find('div#div2', 0);
assert($e->first_child()==null);
assert($e->last_child()==null);

$e = $html->find('div#div0 div#div00', 0);
assert($e->first_child()==null);
assert($e->last_child()==null);
assert($e->next_sibling()==null);
assert($e->prev_sibling()==null);
// -----------------------------------------------
$str = <<<HTML
<div id="div0">
    <div id="div00"></div>
</div>
<div id="div1">
    <div id="div10"></div>
    <div id="div11">
        <div id="div110"></div>
        <div id="div111">
            <div id="div1110"></div>
            <div id="div1111"></div>
            <div id="div1112"></div>
        </div>
        <div id="div112"></div>
    </div>
    <div id="div12"></div>
</div>
<div id="div2"></div>
HTML;
$html->load($str);
assert($html==$str);

assert($html->find("#div1", 0)->id=='div1');
assert($html->find("#div1", 0)->children(0)->id=='div10');
assert($html->find("#div1", 0)->children(1)->children(1)->id=='div111');
assert($html->find("#div1", 0)->children(1)->children(1)->children(2)->id=='div1112');

// -----------------------------------------------------------------------------
// no value attr test
$str = <<<HTML
<form name="form1" method="post" action="">
    <input type="checkbox" name="checkbox0" checked value="checkbox0">aaa<br>
    <input type="checkbox" name="checkbox1" value="checkbox1">bbb<br>
    <input type="checkbox" name="checkbox2" value="checkbox2" checked>ccc<br>
</form>
HTML;
$html->load($str);
assert($html==$str);

$counter = 0;
foreach($html->find('input[type=checkbox]') as $checkbox) {
    if (isset($checkbox->checked)) {
        assert($checkbox->value=="checkbox$counter");
        $counter += 2;
    }
}

$counter = 0;
foreach($html->find('input[type=checkbox]') as $checkbox) {
    if ($checkbox->checked) {
        assert($checkbox->value=="checkbox$counter");
        $counter += 2;
    }
}

$es = $html->find('input[type=checkbox]');
$es[1]->checked = true;
assert($es[1]->outertext=='<input type="checkbox" name="checkbox1" value="checkbox1" checked>');
$es[0]->checked = false;
assert($es[0]=='<input type="checkbox" name="checkbox0" value="checkbox0">');
$es[0]->checked = true;
assert($es[0]->outertext=='<input type="checkbox" name="checkbox0" checked value="checkbox0">');

// -----------------------------------------------------------------------------
// remove attr test
$str = <<<HTML
<input type="checkbox" name="checkbox0">
<input type = "checkbox" name = 'checkbox1' value = "checkbox1">
HTML;

$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox0]', 0);
$e->name = null;
assert($e=='<input type="checkbox">');
$e->type = null;
assert($e=='<input>');

// -----------------------------------------------
$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox0]', 0);
$e->name = null;
assert($e=='<input type="checkbox">');
$e->type = null;
assert($e=='<input>');

// -----------------------------------------------
$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox1]', 0);
$e->value = null;
assert($e=="<input type = \"checkbox\" name = 'checkbox1'>");
$e->type = null;
assert($e=="<input name = 'checkbox1'>");
$e->name = null;
assert($e=='<input>');

$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox1]', 0);
$e->type = null;
assert($e=="<input name = 'checkbox1' value = \"checkbox1\">");
$e->name = null;
assert($e=='<input value = "checkbox1">');
$e->value = null;
assert($e=='<input>');

// -----------------------------------------------------------------------------
// remove no value attr test
$str = <<<HTML
<input type="checkbox" checked name='checkbox0'>
<input type="checkbox" name='checkbox1' checked>
HTML;
$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox1]', 0);
$e->type = NULL;
assert($e=="<input name='checkbox1' checked>");
$e->name = null;
assert($e=="<input checked>");
$e->checked = NULL;
assert($e=="<input>");

// -----------------------------------------------
$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox0]', 0);
$e->type = NULL;
assert($e=="<input checked name='checkbox0'>");
$e->name = NULL;
assert($e=='<input checked>');
$e->checked = NULL;
assert($e=='<input>');

$html->load($str);
assert($html==$str);
$e = $html->find('[name=checkbox0]', 0);
$e->checked = NULL;
assert($e=="<input type=\"checkbox\" name='checkbox0'>");
$e->name = NULL;
assert($e=='<input type="checkbox">');
$e->type = NULL;
assert($e=="<input>");

// -----------------------------------------------------------------------------
// extract text
$str = <<<HTML
<b>okok</b>
HTML;
$html->load($str);
assert($html==$str);
assert($html->plaintext=='okok');

$str = <<<HTML
<div><b>okok</b></div>
HTML;
$html->load($str);
assert($html==$str);
assert($html->plaintext=='okok');

$str = <<<HTML
<div><b>okok</b>
HTML;
$html->load($str);
assert($html==$str);
assert($html->plaintext=='okok');

$str = <<<HTML
<b>okok</b></div>
HTML;
$html->load($str);
assert($html==$str);
assert($html->plaintext=='okok</div>');


// -----------------------------------------------------------------------------
// old fashion camel naming conventions test
$str = <<<HTML
<input type="checkbox" id="checkbox" name="checkbox" value="checkbox" checked>
<input type="checkbox" id="checkbox1" name="checkbox1" value="checkbox1">
<input type="checkbox" id="checkbox2" name="checkbox2" value="checkbox2" checked>
HTML;
$html->load($str);
assert($html==$str);

assert($html->getElementByTagName('input')->hasAttribute('checked')==true);
assert($html->getElementsByTagName('input', 1)->hasAttribute('checked')==false);
assert($html->getElementsByTagName('input', 1)->hasAttribute('not_exist')==false);

assert($html->find('input', 0)->value==$html->getElementByTagName('input')->getAttribute('value'));
assert($html->find('input', 1)->value==$html->getElementsByTagName('input', 1)->getAttribute('value'));

assert($html->find('#checkbox1', 0)->value==$html->getElementById('checkbox1')->getAttribute('value'));
assert($html->find('#checkbox2', 0)->value==$html->getElementsById('checkbox2', 0)->getAttribute('value'));

$e = $html->find('[name=checkbox]', 0);
assert($e->getAttribute('value')=='checkbox');
assert($e->getAttribute('checked')==true);
assert($e->getAttribute('not_exist')=='');

$e->setAttribute('value', 'okok');
assert($e=='<input type="checkbox" id="checkbox" name="checkbox" value="okok" checked>');

$e->setAttribute('checked', false);
assert($e=='<input type="checkbox" id="checkbox" name="checkbox" value="okok">');

$e->setAttribute('checked', true);
assert($e=='<input type="checkbox" id="checkbox" name="checkbox" value="okok" checked>');

$e->removeAttribute('value');
assert($e=='<input type="checkbox" id="checkbox" name="checkbox" checked>');

$e->removeAttribute('checked');
assert($e=='<input type="checkbox" id="checkbox" name="checkbox">');

// -----------------------------------------------
$str = <<<HTML
<div id="div1">
    <div id="div10"></div>
    <div id="div11"></div>
    <div id="div12"></div>
</div>
HTML;
$html->load($str);
assert($html==$str);

$e = $html->find('div#div1', 0);
assert($e->firstChild()->getAttribute('id')=='div10');
assert($e->lastChild()->getAttribute('id')=='div12');
assert($e->nextSibling()==null);
assert($e->previousSibling()==null);

// -----------------------------------------------
$str = <<<HTML
<div id="div0">
    <div id="div00"></div>
</div>
<div id="div1">
    <div id="div10"></div>
    <div id="div11">
        <div id="div110"></div>
        <div id="div111">
            <div id="div1110"></div>
            <div id="div1111"></div>
            <div id="div1112"></div>
        </div>
        <div id="div112"></div>
    </div>
    <div id="div12"></div>
</div>
<div id="div2"></div>
HTML;
$html->load($str);
assert($html==$str);

assert($html->getElementById("div1")->hasAttribute('id')==true);
assert($html->getElementById("div1")->hasAttribute('not_exist')==false);

assert($html->getElementById("div1")->getAttribute('id')=='div1');
assert($html->getElementById("div1")->childNodes(0)->getAttribute('id')=='div10');
assert($html->getElementById("div1")->childNodes(1)->childNodes(1)->getAttribute('id')=='div111');
assert($html->getElementById("div1")->childNodes(1)->childNodes(1)->childNodes(2)->getAttribute('id')=='div1112');

assert($html->getElementsById("div1", 0)->childNodes(1)->id=='div11');
assert($html->getElementsById("div1", 0)->childNodes(1)->childNodes(1)->getAttribute('id')=='div111');
assert($html->getElementsById("div1", 0)->childNodes(1)->childNodes(1)->childNodes(1)->getAttribute('id')=='div1111');

// -----------------------------------------------
$str = <<<HTML
<ul class="menublock">
    </li>
        <ul>
            <li>
                <a href="http://www.cyberciti.biz/tips/pollsarchive">Polls Archive</a>
            </li>
        </ul>
    </li>
</ul>
HTML;
$html->load($str);

$ul = $html->find('ul', 0);
assert($ul->first_child()->tag==='ul');

// -----------------------------------------------
$str = <<<HTML
<ul>
    <li>Item 1 
        <ul>
            <li>Sub Item 1 </li>
            <li>Sub Item 2 </li>
        </ul>
    </li>
    <li>Item 2 </li>
</ul>
HTML;

$html->load($str);
assert($html==$str);

$ul = $html->find('ul', 0);
assert($ul->first_child()->tag==='li');
assert($ul->first_child()->next_sibling()->tag==='li');
// -----------------------------------------------------------------------------
// tear down
$html->clear();
unset($html);
?>