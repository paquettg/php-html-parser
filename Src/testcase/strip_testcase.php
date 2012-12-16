<?php
// $Rev: 169 $
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$dom = new simple_html_dom;

// -----------------------------------------------------------------------------
// comments test
$str = <<<HTML
<div class="class0" id="id0" >
    <!--
        <input type=submit name="btnG" value="go" onclick='goto("url0")'>
    -->
</div>
HTML;
$dom->load($str);
assert(count($dom->find('input'))==0);

// -----------------------------------------------------------------------------
// <code> test
$str = <<<HTML
<div class="class0" id="id0" >
    <CODE>
        <input type=submit name="btnG" value="go" onclick='goto("url0")'>
    </CODE>
</div>
HTML;
$dom->load($str);
assert(count($dom->find('code'))==1);
assert(count($dom->find('input'))==0);

// -----------------------------------------------------------------------------
// <pre> & <code> test
$str = <<<HTML
<PRE><CODE CLASS=Java>
    <input type=submit name="btnG" value="go" onclick='goto("url0")'>
</CODE></PRE>
HTML;
$dom->load($str);
assert(count($dom->find('pre'))==1);
assert(count($dom->find('input'))==0);

// -----------------------------------------------------------------------------
// <script> & <style> test
$str = <<<HTML
<script type="text/javascript" src="test.js"></script>
<script type="text/javascript" src="test.js"/>

<style type="text/css">
@import url("style.css");
</style>

<script type="text/javascript">
var foo = "bar";
</script>
HTML;
$dom->load($str);
assert(count($dom->find('style'))==1);
assert(count($dom->find('script'))==3);

// -----------------------------------------------------------------------------
// php short tag test
$str = <<<HTML
<a href="<?=h('ok')?>">hello</a>
<input type=submit name="btnG" value="<?php echoh('ok')?>">
HTML;
$dom->load($str);
assert($dom->find('a', 0)->href==="<?=h('ok')?>");
assert($dom->find('input', 0)->value==="<?php echoh('ok')?>");

// -----------------------------------------------------------------------------
// noise stripping test
$str = <<<HTML
<!--
<img class="class0" id="id0" src="src0">-->
<img class="class1" id="id1" src="src1">
<!--<img class="class2" id="id2" src="src2">
-->
HTML;
$dom->load($str);
assert(count($dom->find('img'))==1);
assert($dom==$str);
// -----------------------------------------------
$str = <<<HTML
<script type="text/javascript" src="test1.js">ss</script>
<script type="text/javascript" src="test2.js"/>
<script type="text/javascript" src="test3.js" />
<script type="text/javascript" src="test4.js" 
/>

<script type="text/javascript" src="test5.js"/>

<style>
@import url("style1.css");
</style>

<script>
var foo = "bar";
</script>

<style type="text/css">
@import url("style2.css");
</style>

<style>
div,td,.n a,.n a:visited{color:#000}.ts td,.tc{padding:0}.ts,.tb{border-collapse:collapse}.ti,.bl{display:inline}.ti{display:inline-table}.f,.m{color:#666}.flc,a.fl{color:#77c}a,.w,.q:visited,.q:active,.q,.b a,.b a:visited,.mblink:visited{color:#00c}a:visited{color:#551a8b}a:active{color:red}.t{background:#d5ddf3;
color:#000;
padding:5px 1px 4px}.bb{border-bottom:1px solid #36c}.bt{border-top:1px solid #36c}.j{width:34em}.h{color:#36c}.i{color:#a90a08}.a{color:green}.z{display:none}div.n{margin-top:1ex}.n a,.n .i{font-size:10pt}.n .i,.b a{font-weight:bold}.b a{font-size:12pt}.std{font-size:82%}#np,#nn,.nr,#logo span,.ch{cursor:pointer;cursor:hand}.ta{padding:3px 3px 3px 5px}#tpa2,#tpa3{padding-top:9px}#gbar{float:left;height:22px;padding-left:2px}.gbh,.gb2 div{border-top:1px solid #c9d7f1;
</style>

<!-- BEGIN ADVERTPRO ADVANCED CODE BLOCK -->

<script language="JavaScript" type="text/javascript">
<!--
document.write('<SCR'+'IPT src="zone?zid=159&pid=0&random='+Math.floor(89999999*Math.random()+10000000)+'&millis='+new Date().getTime()+'" language="JavaScript" type="text/javascript"></SCR'+'IPT>');
//-->
</script>

<!-- END ADVERTPRO ADVANCED CODE BLOCK -->

<script type="text/javascript">
var foo = "bar";
</script>
HTML;
$dom->load($str);
assert(count($dom->find('script'))==8);
assert(count($dom->find('style'))==3);
//echo "\n\n\n\n".$dom->save();
assert($dom==$str);

// -----------------------------------------------------------------------------
// tear down
$dom->clear();
unset($dom);
?>