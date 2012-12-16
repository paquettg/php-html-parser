<?php
// -----------------------------------------------------------------------------
// setup
error_reporting(E_ALL);
require_once('../simple_html_dom.php');
$dom = new simple_html_dom;

// -----------------------------------------------------------------------------
//self-closing tags test
$str = <<<HTML
<hr>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
assert($e->outertext=='<hr id="foo">');
// -----------------------------------------------
$str = <<<HTML
<hr/>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
assert($e->outertext=='<hr id="foo"/>');
// -----------------------------------------------
$str = <<<HTML
<hr />
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
assert($e->outertext=='<hr id="foo" />');
// -----------------------------------------------
$str = <<<HTML
<hr>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" class="bar">');
// -----------------------------------------------
$str = <<<HTML
<hr/>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" class="bar"/>');
// -----------------------------------------------
$str = <<<HTML
<hr />
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->id= 'foo';
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" class="bar" />');
// -----------------------------------------------
$str = <<<HTML
<hr id="foo" kk=ll>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" kk=ll class="bar">');
// -----------------------------------------------
$str = <<<HTML
<hr id="foo" kk="ll"/>
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" kk="ll" class="bar"/>');
// -----------------------------------------------
$str = <<<HTML
<hr id="foo" kk=ll />
HTML;
$dom->load($str);
$e = $dom->find('hr', 0);
$e->class = 'bar';
assert($e->outertext=='<hr id="foo" kk=ll class="bar" />');

// -----------------------------------------------
$str = <<<HTML
<div><nobr></div>
HTML;
$dom->load($str);
$e = $dom->find('nobr', 0);
assert($e->outertext=='<nobr>');

// -----------------------------------------------------------------------------
// optional closing tags test
$str = <<<HTML
<body>
</b><.b></a>
</body>
HTML;
$dom = str_get_html($str);
assert($dom->find('body', 0)->outertext==$str);

// -----------------------------------------------
$str = <<<HTML
<html>
    <body>
        <a>foo</a>
        <a>foo2</a>
HTML;
$dom = str_get_html($str);
assert($dom==$str);
assert($dom->find('html body a', 1)->innertext=='foo2');

// -----------------------------------------------
$str = <<<HTML
HTML;
$dom = str_get_html($str);
assert($dom==$str);
assert($dom->find('html a', 1)===null);
//assert($dom->find('html a', 1)->innertext=='foo2');

// -----------------------------------------------
$str = <<<HTML
<body>
<div>
</body>
HTML;
$dom = str_get_html($str);
assert($dom==$str);
assert($dom->find('body', 0)->outertext==$str);

// -----------------------------------------------
$str = <<<HTML
<body>
<div> </a> </div>
</body>
HTML;
$dom = str_get_html($str);

assert($dom->find('body', 0)->outertext==$str);

// -----------------------------------------------
$str = <<<HTML
<table>
    <tr>
        <td><b>aa</b>
    <tr>
        <td><b>bb</b>
</table>
HTML;
$dom = str_get_html($str);

assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<table>
<tr><td>1<td>2<td>3
</table>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('td'))==3);
assert($dom->find('td', 0)->innertext=='1');
assert($dom->find('td', 0)->outertext=='<td>1');
assert($dom->find('td', 1)->innertext=='2');
assert($dom->find('td', 1)->outertext=='<td>2');
assert($dom->find('td', 2)->innertext=="3\r\n");
assert($dom->find('td', 2)->outertext=="<td>3\r\n");

// -----------------------------------------------
$str = <<<HTML
<table>
<tr>
    <td><b>1</b></td>
    <td><b>2</b></td>
    <td><b>3</b></td>
</table>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('tr td'))==3);

// -----------------------------------------------
$str = <<<HTML
<table>
<tr><td><b>11</b></td><td><b>12</b></td><td><b>13</b></td>
<tr><td><b>21</b></td><td><b>32</b></td><td><b>43</b></td>
</table>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('tr'))==2);
assert(count($dom->find('tr td'))==6);
assert($dom->find('tr', 1)->outertext=="<tr><td><b>21</b></td><td><b>32</b></td><td><b>43</b></td>\r\n");
assert($dom->find('tr', 1)->innertext=="<td><b>21</b></td><td><b>32</b></td><td><b>43</b></td>\r\n");
assert($dom->find('tr', 1)->plaintext=="213243\r\n");

// -----------------------------------------------
$str = <<<HTML
<p>1
<p>2</p>
<p>3
HTML;
$dom = str_get_html($str);
assert(count($dom->find('p'))==3);
assert($dom->find('p', 0)->innertext=="1\r\n");
assert($dom->find('p', 0)->outertext=="<p>1\r\n");
assert($dom->find('p', 1)->innertext=="2");
assert($dom->find('p', 1)->outertext=="<p>2</p>");
assert($dom->find('p', 2)->innertext=="3");
assert($dom->find('p', 2)->outertext=="<p>3");

// -----------------------------------------------
$str = <<<HTML
<nobr>1
<nobr>2</nobr>
<nobr>3
HTML;
$dom = str_get_html($str);
assert(count($dom->find('nobr'))==3);
assert($dom->find('nobr', 0)->innertext=="1\r\n");
assert($dom->find('nobr', 0)->outertext=="<nobr>1\r\n");
assert($dom->find('nobr', 1)->innertext=="2");
assert($dom->find('nobr', 1)->outertext=="<nobr>2</nobr>");
assert($dom->find('nobr', 2)->innertext=="3");
assert($dom->find('nobr', 2)->outertext=="<nobr>3");

// -----------------------------------------------
$str = <<<HTML
<dl><dt>1<dd>2<dt>3<dd>4</dl>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('dt'))==2);
assert(count($dom->find('dd'))==2);
assert($dom->find('dt', 0)->innertext=="1");
assert($dom->find('dt', 0)->outertext=="<dt>1");
assert($dom->find('dt', 1)->innertext=="3");
assert($dom->find('dt', 1)->outertext=="<dt>3");
assert($dom->find('dd', 0)->innertext=="2");
assert($dom->find('dd', 0)->outertext=="<dd>2");
assert($dom->find('dd', 1)->innertext=="4");
assert($dom->find('dd', 1)->outertext=="<dd>4");

// -----------------------------------------------
$str = <<<HTML
<dl id="dl1"><dt>11<dd>12<dt>13<dd>14</dl>
<dl id="dl2"><dt>21<dd>22<dt>23<dd>24</dl>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('#dl1 dt'))==2);
assert(count($dom->find('#dl2  dd'))==2);
assert($dom->find('dl', 0)->innertext=="<dt>11<dd>12<dt>13<dd>14");
assert($dom->find('dl', 1)->innertext=="<dt>21<dd>22<dt>23<dd>24");

// -----------------------------------------------
$str = <<<HTML
<ul id="ul1"><li><b>1</b><li><b>2</b></ul>
<ul id="ul2"><li><b>3</b><li><b>4</b></ul>
HTML;
$dom = str_get_html($str);
assert(count($dom->find('ul[id=ul1] li'))==2);

// -----------------------------------------------------------------------------
// invalid test
$str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <img class="class0" id="id0" src="src0">
    </img>
    <img class="class0" id="id0" src="src0">
    </div>
</div>
HTML;
$dom->load($str);
assert(count($dom->find('img'))==2);
assert(count($dom->find('img'))==2);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <span></span>
    </span>
    <span></span>
    </div>
</div>
HTML;

$dom->load($str);
assert(count($dom->find('span'))==2);
assert(count($dom->find('div'))==2);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <span></span>
    <span>
    <span></span>
    </div>
</div>
HTML;
$dom->load($str);
assert(count($dom->find('span'))==3);
assert(count($dom->find('div'))==2);
assert($dom==$str);

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
$dom->load($str);
assert(count($dom->find('ul'))==2);
assert(count($dom->find('ul ul'))==1);
assert(count($dom->find('li'))==1);
assert(count($dom->find('a'))==1);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<td>
    <div>
        </span>
    </div>
</td>
HTML;
$dom->load($str);
assert(count($dom->find('td'))==1);
assert(count($dom->find('div'))==1);
assert(count($dom->find('td div'))==1);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<td>
    <div>
        </b>
    </div>
</td>
HTML;
$dom->load($str);
assert(count($dom->find('td'))==1);
assert(count($dom->find('div'))==1);
assert(count($dom->find('td div'))==1);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<td>
    <div></div>
    </div>
</td>
HTML;
$dom->load($str);
assert(count($dom->find('td'))==1);
assert(count($dom->find('div'))==1);
assert(count($dom->find('td div'))==1);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<html>
    <body>
        <table>
            <tr>
                foo</span>
                <span>bar</span>
                </span>important
            </tr>
        </table>
    </bod>
</html>
HTML;
$dom->load($str);
assert(count($dom->find('table span'))===1);
assert($dom->find('table span', 0)->innertext==='bar');
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<td>
    <div>
        <font>
            <b>foo</b>
    </div>
</td>
HTML;
$dom->load($str);
assert(count($dom->find('td div font b'))==1);
assert($dom==$str);

// -----------------------------------------------
$str = <<<HTML
<span style="okokok">
... then slow into 287 
    <i> 
        <b> 
            <font color="#0000CC">(hanover0...more volume between 202 & 53 
            <i> 
                <b> 
                    <font color="#0000CC">(parsippany)</font> 
                </b>
            </i>
            ...then sluggish in spots out to dover chester road 
            <i> 
                <b> 
                    <font color="#0000CC">(randolph)</font> 
                </b> 
            </i>..then traffic light delays out to route 46 
            <i> 
                <b> 
                    <font color="#0000CC">(roxbury)</font> 
                </b> 
            </i>/eb slow into 202 
            <i> 
                <b> 
                    <font color="#0000CC">(morris plains)</font> 
                </b> 
            </i> & again into 287 
            <i> 
                <b> 
                    <font color="#0000CC">(hanover)</font>
                </b> 
            </i> 
</span>. 
<td class="d N4 c">52</td> 
HTML;
$dom->load($str);
assert(count($dom->find('span td'))==0);
assert($dom==$str);

// -----------------------------------------------------------------------------
// invalid '<'
// -----------------------------------------------
$str = <<<HTML
<td><b>test :</b>1 gram but <5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but <5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but <5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but<5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but<5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but<5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but< 5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but< 5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but< 5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but < 5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but < 5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but < 5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5< grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5< grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5< grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 < grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 < grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 < grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 <grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 <grams');
assert($dom==$str);
// -----------------------------------------------
$str = <<<HTML
<td><b>test :</b>1 gram but 5< grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5< grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5< grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but5< grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but5< grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but5< grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 <grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 <grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5<grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5<grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5<grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 <grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 <grams');
assert($dom==$str);

// -----------------------------------------------------------------------------
// invalid '>'
// -----------------------------------------------
$str = <<<HTML
<td><b>test :</b>1 gram but >5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but >5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but >5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but>5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but>5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but>5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but> 5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but> 5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but> 5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but > 5 grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but > 5 grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but > 5 grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5> grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5> grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5> grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 > grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 > grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 > grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 >grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 >grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 >grams');
assert($dom==$str);
// -----------------------------------------------
$str = <<<HTML
<td><b>test :</b>1 gram but 5> grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5> grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5> grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but5> grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but5> grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but5> grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 >grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 >grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 >grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5>grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5>grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5>grams');
assert($dom==$str);

$str = <<<HTML
<td><b>test :</b>1 gram but 5 >grams</td>
HTML;
$dom->load($str);
assert($dom->find('td', 0)->innertext==='<b>test :</b>1 gram but 5 >grams');
assert($dom->find('td', 0)->plaintext==='test :1 gram but 5 >grams');
assert($dom==$str);

// -----------------------------------------------------------------------------
// BAD HTML test
$str = <<<HTML
<strong class="see <a href="http://www.oeb.harvard.edu/faculty/girguis/">http://www.oeb.harvard.edu/faculty/girguis/</a>">.</strong></p> 
HTML;
$dom->load($str);
// -----------------------------------------------
$str = <<<HTML
<a href="http://www.oeb.harvard.edu/faculty/girguis\">http://www.oeb.harvard.edu/faculty/girguis/</a>">
HTML;
$dom->load($str);
// -----------------------------------------------
$str = <<<HTML
<strong class="''""";;''""";;\"\''''\"""''''""''>""'''"'" '
HTML;
$dom->load($str);
// -----------------------------------------------------------------------------
// tear down
$dom->clear();
unset($dom);
?>