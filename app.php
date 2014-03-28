<?php

require 'vendor/autoload.php';

use Silex\Application;

use Mustache\Silex\Provider\MustacheServiceProvider;
use Lstr\Silex\Asset\AssetServiceProvider;
use Lstr\Silex\Config\ConfigServiceProvider;

use Douglas\Request\Report;

$app = new Application();
$app->register(new ConfigServiceProvider);
$app['config'] = $app['lstr.config']->load([
    __DIR__ . '/config/dev.php',
]);
$app['debug'] = $app['config']['debug'];
$app->register(new AssetServiceProvider);
$app['lstr.asset.path'] = [
    'app'    => __DIR__ . '/assets/app',
    'lib'    => __DIR__ . '/assets/lib',
    'vendor' => __DIR__ . '/assets/vendor',
];
$app->register(new MustacheServiceProvider, [
    'mustache.path'    => __DIR__ . '/templates',
    'mustache.options' => [
        'cache' => __DIR__ . '/tmp/cache/mustache',
    ],
    'mustache.helpers' => [
        'css' => function ($text) use ($app) {
            return $app['lstr.asset']->cssTag($text);
        },
        'js' => function ($text) use ($app) {
            return $app['lstr.asset']->jsTag($text);
        },
    ],
]);

$app->get('/', function (Application $app) {
    $name = $app['request']->query->get('name');

    if ($name) {
        $pdf = $app['request']->query->has('pdf');
        $format = ($pdf ? Report::FORMAT_PDF : Report::FORMAT_HTML);
        $parameters = [
            'j_username'     => $app['config']['jasper.username'],
            'j_password'     => $app['config']['jasper.password'],
            'name'           => $name,
        ];

        $report = new Report([
            'jasper_url' => $app['config']['jasper.url'],
            'report_url' => '/Reports/HelloWorld',
            'parameters' => $parameters,
            'format'     => $format,
        ]);

        $key = md5(serialize($parameters));
        $file_name = "{$report->getPrettyUrl()}_{$key}.{$format}";
        $path = __DIR__ . "/tmp/cache/{$file_name}";

        if (!file_exists($path)) {

            $report->send();

            if ($format == Report::FORMAT_HTML) {
                $reportData = $report->getHtml(function ($asset_url, $jsessionid) use ($app) {
                    $asset = new Douglas\Request\Asset([
                        'jasper_url' => $app['config']['jasper.url'],
                        'jsessionid' => $jsessionid,
                        'asset_url'  => str_replace('/jasperserver-pro', '', $asset_url),
                        'parameters' => [
                            'j_username' => $app['config']['jasper.username'],
                            'j_password' => $app['config']['jasper.password'],
                        ]
                    ]);
                    $asset->send();

                    // Currently I will only support images
                    if ($asset->getHeader('content-type') != 'image/png') {
                        return false;
                    }

                    $asset_file_name = sprintf('jasper_report_asset_%s', uniqid());
                    $full_asset_path = __DIR__ . "tmp/cache/{$asset_file_name}";

                    file_put_contents($full_asset_path, $asset->getBody());

                    return "/jasper-asset/?name={$asset_file_name}";
                });
            } else {
                $reportData = $report->getBody();
            }
            file_put_contents($path, $reportData);
        }

        if ($format === Report::FORMAT_HTML) {
            if (!isset($reportData)) {
                $reportData = file_get_contents($path);
            }
        } elseif ($format === Report::FORMAT_PDF) {
            return $app->sendFile($path);
        }
    }

    return $app['mustache']->render('homepage', [
        'reportHtml' => $reportData ?: null,
        'name'       => $name,
    ]);
});

$app->get('/assets/{name}', function ($name, Application $app) {
    return $app['lstr.asset.responder']->getResponse($name);
})->assert('name', '.*');

$app->get('/jasper-asset', function (Application $app) {
    $name = $app['request']->query->get('name');
    header("Content-type: image/png");
    readfile(__DIR__ . "/tmp/cache/{$name}");
});

return $app;
