<?php
// $Rev$
// -----------------------------------------------------------------------------
error_reporting(E_ALL);

include_once('../../simple_html_dom_reader.php');

$filename = '../html/google.htm';

function dump_memory() {
    echo 'peak: ' . number_format(memory_get_peak_usage(), 0, '.', ',') . ' bytes, end: ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes<br>";
}

function stat_dom($dom) {
    $count_text = 0;
    $count_comm = 0;
    $count_elem = 0;
    $count_tag_end = 0;
    
    foreach($dom->nodes as $n) {
        if ($n->nodetype==HDOM_TYPE_TEXT)
            ++$count_text;
        if ($n->nodetype==HDOM_TYPE_COMMENT)
            ++$count_comm;
        if ($n->nodetype==HDOM_TYPE_ELEMENT)
            ++$count_elem;
        if ($n->nodetype==HDOM_TYPE_ENDTAG)
            ++$count_tag_end;
    }
    
    echo 'Total: '. count($dom->nodes).', Text: '.$count_text.', Commnet: '.$count_comm.', Tag: '.$count_elem.', End Tag: '.$count_tag_end.'<br>';
}

echo 'init ' . number_format(memory_get_usage(), 0, '.', ',') . " bytes";


echo '<br><br>[load file]<br>init memory: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
echo '------------------------------------------<br>';
flush();
for($i=0; $i<3; ++$i) {
    $str = file_get_contents($filename);
    unset($str);
    dump_memory();
}
echo 'final: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
flush();


$str = file_get_contents($filename);
echo '<br><br>[multi objects str_get_dom clear memory]<br>init memory: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
echo '------------------------------------------<br>';
flush();
for($i=0; $i<3; ++$i) {
    $dom = str_get_dom($str);
    //stat_dom($dom);
    $dom->clear();
    unset($dom);
    dump_memory();
    flush();
}
echo 'final: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
flush();

echo '<br><br>[multi objects file_get_dom clear memory]<br>init memory: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
echo '------------------------------------------<br>';
flush();
for($i=0; $i<3; ++$i) {
    $dom = file_get_dom($filename);
    //stat_dom($dom);
    $dom->clear();
    unset($dom);
    dump_memory();
    flush();
}
echo 'final: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
flush();


echo '<br><br>[one object]<br>init memory: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
echo '------------------------------------------<br>';
flush();
$dom = new simple_html_dom;
for($i=0; $i<3; ++$i) {
    $dom->load_file($filename);
    $dom->clear();
    dump_memory();
}
unset($dom);
echo 'final: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
flush();



echo '<br><br>[multi objects without clear memory]<br>init memory: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
echo '------------------------------------------<br>';
flush();
for($i=0; $i<3; ++$i) {
    $dom = file_get_dom($filename);
    dump_memory();
}
echo 'final: '.number_format(memory_get_usage(), 0, '.', ',').'<br>';
flush();
?>