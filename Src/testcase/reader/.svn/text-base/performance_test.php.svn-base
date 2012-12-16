<?php
// $Rev$
// -----------------------------------------------------------------------------
error_reporting(E_ALL);

include_once('../../simple_html_dom_reader.php');

$all = 0;
$min = 10000;
$max = 0;
$count = 20;

$str = file_get_contents('../html/google.htm');
$dom = new simple_html_dom;

for ($i=0; $i<$count; ++$i) {
    $start = microtime();
    $dom->load($str, false);
    list($eu, $es) = explode(' ', microtime());
    list($bu, $bs) = explode(' ', $start);

    if (((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000 > $max)
        $max = ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000;

    if (((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000 < $min)
        $min = ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000;
    
    $all += ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000;
    echo sprintf('(%.1f)', ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000).'<br>';
    $dom->clear();
}

echo '<br>-------------------------<br>';
echo 'min: ' . $min . '<br>';
echo 'max: ' . $max . '<br>';

echo '<br>avg: ' . $all/$count . '<br>';
?>