<?php
// $Rev$
error_reporting(E_ALL);

foreach (new DirectoryIterator(getcwd()) as $entry) {
    if ($entry->isFile() && strpos($entry, '_testcase.')>0) {
        echo basename($entry);
        require_once($entry);
        echo '<br>...pass!<br><br>';
    }
}
?>