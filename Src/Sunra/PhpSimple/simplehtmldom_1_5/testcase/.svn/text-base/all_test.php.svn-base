<?php
// $Rev$
error_reporting(E_ALL);

function quick_test($html_dom, $str, $selector, $params=array('inner'=>'', 'plain'=>'', 'outer'=>'')) {
    $html_dom->load($str);
    $e = $html_dom->find($selector, 0);
    if (isset($params['inner']))
        assert($e->innertext===$params['inner']);
    if (isset($params['plain']))
        assert($e->plaintext===$params['plain']);
    if (isset($params['outer']))
        assert($e->outertext===$params['outer']);
    assert($html_dom==$str);
}

foreach (new DirectoryIterator(getcwd()) as $entry) {
    if ($entry->isFile() && strpos($entry, '_testcase.')>0) {
        echo basename($entry);
        require_once($entry);
        echo '<br>...pass!<br><br>';
    }
}
?>