<?php

require "vendor/autoload.php";

$words = [];

for ($i = 1; $i < $argc; $i++) {
    $words[$i - 1] = $argv[$i];
}

$parser = new \App\Main($words[0] ?? 'tesla');

header('Content-type: application/json');

echo $parser->dispatch();