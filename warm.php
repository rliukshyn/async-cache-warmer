<?php
require_once 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$client = initializeClient($loop);

$requestLimit = $argv[2] ?? null;
if (empty($argv[1])) {
    echo 'Please specify url to sitemap.xml' . PHP_EOL;
    die;
}
$urls = getSitemapData($argv[1], $requestLimit);
if (isset($argv[2])) {
    $chunks = $urls;
    foreach ($chunks as $chunk) {
        sendRequests($chunk, $client, $loop);
    }
} else {
    sendRequests($urls, $client, $loop);
}
/**
 * @param array $chunk
 * @param \React\HttpClient\Client $client
 * @param \React\EventLoop\StreamSelectLoop $loop
 */
function sendRequests(array $chunk, \React\HttpClient\Client $client, \React\EventLoop\StreamSelectLoop $loop)
{
    foreach ($chunk as $url) {
        $request = $client->request('GET', $url);
        $request->on('response', function (\React\HttpClient\Response $response) use ($url) {
            $code = $response->getCode();
            $reason = $response->getReasonPhrase();
            echo $url . ' - ' . $code . ' ' . $reason . PHP_EOL;
        });
        $request->end();
    }
    $loop->run();
}

/**
 * @param \React\EventLoop\StreamSelectLoop $loop
 * @return \React\HttpClient\Client
 */
function initializeClient(\React\EventLoop\StreamSelectLoop $loop) : \React\HttpClient\Client
{
    $dnsResolverFactory = new React\Dns\Resolver\Factory();
    $dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

    $factory = new React\HttpClient\Factory();
    return $factory->create($loop, $dnsResolver);
}

/**
 * @param string $sitemapUrl
 * @param string|null $requestLimit
 * @return array
 */
function getSitemapData(string $sitemapUrl, string $requestLimit = null) : array
{
    $urls= [];
    $sitemap = simplexml_load_file($sitemapUrl);
    foreach ($sitemap as $element) {
        $urls[] = $element->loc;
    }
    if (isset($requestLimit)) {
        $urls = array_chunk($urls, $requestLimit);
    }
    return $urls;
}
