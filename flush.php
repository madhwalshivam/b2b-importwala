<?php
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->select(1);
$redis->flushDB();
echo "Cache Redis DB Flushed!\n";
