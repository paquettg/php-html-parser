<?php
// $Rev: 133 $
error_reporting(E_ALL);

include_once('../simple_html_dom.php');

$all = 0;
$min = 10000;
$max = 0;
$count = 20;

$str = file_get_contents('./html/google.htm');
$html = new simple_html_dom;

for ($i=0; $i<$count; ++$i) {
    $start = microtime();

    $html->load($str, false);

    list($eu, $es) = explode(' ', microtime());
    list($bu, $bs) = explode(' ', $start);

    $diff = ((float)$eu+(float)$es-(float)$bu-(float)$bs)*1000;

    if ($diff>$max)
        $max = $diff;

    if ($diff<$min)
        $min = $diff;

    $all += $diff;
    echo sprintf('(%.1f)', $diff).'<br>';
}

echo '<br>-------------------------<br>';
echo 'min: ' . $min . '<br>';
echo 'max: ' . $max . '<br>';

echo '<br>avg: ' . $all/$count . '<br>';
?>