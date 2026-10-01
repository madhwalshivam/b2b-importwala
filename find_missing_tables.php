<?php
$tables = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('c:\xampp\htdocs\importwala\app'));
foreach ($it as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        preg_match_all('/(?:INTO|FROM|UPDATE|JOIN|TABLE)\s+`?([a-zA-Z0-9_]+)`?/i', $content, $m);
        if (!empty($m[1])) {
            foreach ($m[1] as $t) {
                $tables[strtolower($t)] = true;
            }
        }
    }
}
$db = new PDO('mysql:host=localhost;dbname=importwala', 'root', '');
$actual = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$missing = array_diff(array_keys($tables), $actual);

// Filter out SQL keywords
$keywords = ['where', 'set', 'by', 'as', 'limit', 'left', 'right', 'inner', 'outer', 'cross', 'order', 'group', 'having', 'select', 'insert', 'update', 'delete', 'and', 'or', 'null', 'not', 'true', 'false', 'is', 'in', 'values', 'if', 'exists', 'table', 'like', 'this', 'id', 'name', 'status', 'created_at', 'updated_at', 'user_id', 'product_id'];
$missing = array_diff($missing, $keywords);

// Filter out PHP variable names that got caught like "users" if it was $users, wait regex matched words after FROM/INTO
print_r(array_values($missing));
