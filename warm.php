<?php
require_once 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$urls= [];
$sitemap = simplexml_load_file($argv[1]);
foreach ($sitemap as $element) {
    $urls[] = $element->loc;
}
$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new React\HttpClient\Factory();
$client = $factory->create($loop, $dnsResolver);

foreach ($urls as $url) {
    $request = $client->request('GET', $url);
    $request->on('response', function (\React\HttpClient\Response $response) use ($url) {
        $code = $response->getCode();
        $reason = $response->getReasonPhrase();
        echo $url . ' - ' . $code . ' ' . $reason . PHP_EOL;
    });
    $request->end();
}
$loop->run();
