<?php

require "vendor/autoload.php";

$parser = new \App\Main($_GET['word'] ?? 'abc');

header('Content-type: application/json');

echo $parser->dispatch();