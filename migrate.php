<?php
require 'config/database.php';
require 'app/Core/Database.php';
$db = App\Core\Database::getInstance();
try { $db->exec('ALTER TABLE product_images ADD COLUMN source_url TEXT NULL AFTER image_url'); } catch(Exception $e){}
try { $db->exec('ALTER TABLE product_colors ADD COLUMN source_url TEXT NULL AFTER swatch_hex_or_image'); } catch(Exception $e){}
try { $db->exec('ALTER TABLE products ADD COLUMN main_image_source_url TEXT NULL AFTER main_image'); } catch(Exception $e){}
echo "Migrations executed\n";
