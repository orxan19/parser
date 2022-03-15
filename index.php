<?php

require "vendor/autoload.php";

$parser = new \App\Main($_GET['word'] ?? 'tesla');

header('Content-type: application/json');

echo $parser->dispatch();