<?php
$colors = [ ["id" => 3333, "color_name" => "Test's Color", "sizes" => [ ["size_label" => "M"] ] ] ];
$json = json_encode($colors, JSON_UNESCAPED_UNICODE);
echo htmlspecialchars($json, ENT_QUOTES, "UTF-8");
