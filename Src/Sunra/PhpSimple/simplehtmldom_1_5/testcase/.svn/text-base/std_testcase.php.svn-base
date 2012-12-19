<?php
// $Rev$
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$dom = new simple_html_dom;

// -----------------------------------------------------------------------------
// empty test
$str = '';
$dom->load($str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = null;
$dom->load($str);
assert($dom->save()==$str);

// -----------------------------------------------------------------------------
// text test
$str = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"></html>
HTML;
$dom->load($str);
assert(count($dom->find('unknown'))==1);
assert(count($dom->find('text'))==1);

// -----------------------------------------------------------------------------
// string quote test
$str = <<<HTML
<div class="class0" id="id0" >
    okok<br>
    <input type=submit name="btnG" value="go" onclick='goto("url0")'>
    <br/>
    <div><input type=submit name="btnG2" value="go" onclick="goto('url1'+'\'')"/></div>
    <input type=submit name="btnG2" value="go" onclick="goto('url2')"/>
    <div><input type=submit name="btnG2" value="go" onclick='goto("url4"+"\"")'></div>
    <br/>
</div>
HTML;
$dom->load($str);
$es = $dom->find('input');
assert(count($es)==4);
assert($es[0]->onclick=='goto("url0")');
assert($es[1]->onclick=="goto('url1'+'\'')");
assert($es[2]->onclick=="goto('url2')");
assert($es[3]->onclick=='goto("url4"+"\"")');

// -----------------------------------------------------------------------------
// clone test
$str = <<<HTML
<div class="class0" id="id0" >
    okok<br>
    <input type=submit name="btnG" value="go" onclick='goto("url0")'>
    <br/>
    <div><input type=submit name="btnG2" value="go" onclick="goto('url1'+'\'')"/></div>
    <input type=submit name="btnG2" value="go" onclick="goto('url2')"/>
    <div><input type=submit name="btnG2" value="go" onclick='goto("url4"+"\"")'></div>
    <br/>
</div>
HTML;
$dom->load($str);
$es = $dom->find('input');
assert(count($es)==4);
assert($es[0]->onclick=='goto("url0")');
assert($es[1]->onclick=="goto('url1'+'\'')");
assert($es[2]->onclick=="goto('url2')");
assert($es[3]->onclick=='goto("url4"+"\"")');

unset($es);
$dom2 = clone($dom);
$es = $dom2->find('input');
assert(count($es)==4);
assert($es[0]->onclick=='goto("url0")');
assert($es[1]->onclick=="goto('url1'+'\'')");
assert($es[2]->onclick=="goto('url2')");
assert($es[3]->onclick=='goto("url4"+"\"")');

// -----------------------------------------------
$str = <<<HTML
<div class='class0' id="id0" aa='aa' bb="bb" cc='"cc"' dd="'dd'"></div>
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);

// -----------------------------------------------------------------------------
// monkey test
$str = <<<HTML
<
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<

HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML


<
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<a
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<a<
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<<<<ab
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<<<<ab  
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<<><<>ab  
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
<abc

HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
>
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);
// -----------------------------------------------
// $str = <<<HTML
// <abc
// (<1 mol%) 
// HTML;
// $dom->load($str);
// echo $dom;
// assert($dom==$str);
// assert($dom->save()==$str);
// -----------------------------------------------
$str = <<<HTML
(<1 mol%) 
HTML;
$dom->load($str);
assert($dom==$str);
assert($dom->save()==$str);

// -----------------------------------------------------------------------------
// rnadom string test
function str_random($length)
{
    $str = "";
    srand((double)microtime()*1000000);
    $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $char_list .= "abcdefghijklmnopqrstuvwxyz";
    $char_list .= "1234567890";
    $char_list .= "<>!?[]%^&*()";
    for($i=0; $i<$length; ++$i)
        $str .= substr($char_list,(rand()%(strlen($char_list))), 1);
    return $str;
}

for($i=0; $i<60; ++$i) {
    $str = str_random($i);
    //echo $str."\n<br>";
    $dom->load($str, false);
    //echo $dom->save()."\n<br>";
    assert($dom==$str);
}

// -----------------------------------------------------------------------------
// lowercase test
$str = <<<HTML
<img class="class0" id="id0" src="src0">
HTML;
$dom->load($str);
assert(count($dom->find('img'))==1);
assert(count($dom->find('IMG'))==1);
assert(isset($dom->find('img', 0)->class));
assert(!isset($dom->find('img', 0)->CLASS));
assert($dom->find('img', 0)->class=='class0');
assert($dom==$str);
// -----------------------------------------------
$str = <<<HTML
<IMG CLASS="class0" ID="id0" SRC="src0">
HTML;
$dom->load($str);
assert(count($dom->find('img'))==1);
assert(count($dom->find('IMG'))==1);
assert(isset($dom->find('img', 0)->class));
assert(!isset($dom->find('img', 0)->CLASS));
assert($dom->find('img', 0)->class=='class0');
assert($dom==strtolower($str));
// -----------------------------------------------
$str = <<<HTML
<IMG CLASS="class0" ID="id0" SRC="src0">
HTML;
$dom->load($str, false);
assert(count($dom->find('img'))==0);
assert(count($dom->find('IMG'))==1);
assert(isset($dom->find('IMG', 0)->CLASS));
assert(!isset($dom->find('IMG', 0)->class));
assert($dom->find('IMG', 0)->CLASS=='class0');
assert($dom==$str);

// -----------------------------------------------------------------------------
// tear down
$dom->clear();
unset($dom);
?>