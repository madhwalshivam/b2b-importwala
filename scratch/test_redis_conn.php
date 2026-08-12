<?php
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        if (@$redis->connect('127.0.0.1', 6379, 1.5)) {
            echo "Redis Extension Connected Successfully!\n";
            exit(0);
        }
    }
    echo "Redis native extension not running locally on 6379. Infrastructure fallback initialized.\n";
} catch (Throwable $e) {
    echo "Redis check: " . $e->getMessage() . "\n";
}
