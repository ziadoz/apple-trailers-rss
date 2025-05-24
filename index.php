<?php
require_once __DIR__ . '/src/HtmlDownloader.php';
require_once __DIR__ . '/src/HtmlParser.php';
require_once __DIR__ . '/src/RssFeed.php';

const URL = 'https://apple.com/trailers';

$html  = new HtmlDownloader(URL)->download();
$items = new HtmlParser(URL, $html)->parse();
$rss   = new RssFeed(URL, $items)->render();

file_put_contents($output = __DIR__ . '/output/apple-trailers.rss', $rss);

echo 'Generated RSS Feed: ' . $output . PHP_EOL;