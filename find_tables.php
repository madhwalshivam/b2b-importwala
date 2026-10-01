<?php
$tables = [];
$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('c:/xampp/htdocs/importwala/app'));
foreach($dir as $file) {
    if($file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        if(preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+`?([a-zA-Z0-9_]+)`?/i', $content, $matches)) {
            foreach($matches[1] as $t) {
                $tables[strtolower($t)] = true;
            }
        }
        if(preg_match_all('/protected\s+\$table\s*=\s*[\'"]([a-zA-Z0-9_]+)[\'"]/i', $content, $matches)) {
            foreach($matches[1] as $t) {
                $tables[strtolower($t)] = true;
            }
        }
    }
}
if (is_dir('c:/xampp/htdocs/importwala/cli')) {
    $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('c:/xampp/htdocs/importwala/cli'));
    foreach($dir as $file) {
        if($file->getExtension() == 'php') {
            $content = file_get_contents($file->getPathname());
            if(preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+`?([a-zA-Z0-9_]+)`?/i', $content, $matches)) {
                foreach($matches[1] as $t) {
                    $tables[strtolower($t)] = true;
                }
            }
        }
    }
}
$exclude = ['select', 'where', 'left', 'right', 'inner', 'outer', 'set', 'values', 'as', 'and', 'or', 'on', 'group', 'order', 'limit', 'having', 'is', 'null', 'not', 'distinct', 'all', 'desc', 'asc', 'by'];
$clean_tables = [];
foreach(array_keys($tables) as $t) {
    if (!in_array($t, $exclude)) {
        $clean_tables[] = $t;
    }
}
sort($clean_tables);
echo implode("\n", $clean_tables);
