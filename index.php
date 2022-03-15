<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

$url = 'https://search.ipaustralia.gov.au/trademarks/search/result?s=3b0d875a-31be-43f0-992d-6f6cd977b2ac';
$dom = new Dom;
$dom->loadFromUrl($url);
$contents = $dom->find('.classes');
echo count($contents); // 10