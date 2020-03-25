<?php

declare(strict_types=1);

/**
 * Inject the Google Cloud Logging Structured Log format into the PHP-FPM www.conf
 *
 * @link https://cloud.google.com/logging/docs/reference/v2/rest/v2/LogEntry
 * @link http://www.inanzzz.com/index.php/post/6cn7/formatting-php-fpm-and-nginx-access-logs-as-standardised-json-string-in-docker-environment
 */

$accessFormatJson = <<<JSON
{
    "severity": "INFO",
    "httpRequest": {
        "requestMethod": "%m",
        "requestUrl": "%r%Q%q",
        "requestSize": "%l",
        "status": %s,
        "userAgent": "%{HTTP_USER_AGENT}e",
        "remoteIp": "%{X_FORWARDED_FOR}e",
        "serverIp": "%R",
        "referer": "%{HTTP_REFERER}e",
        "latency": "%ds",
        "protocol": "%{SERVER_PROTOCOL}e"
    },
    "cpu": %C,
    "memory": %M
}
JSON;

$format      = str_replace([' ', "\n", '"'], ['', '', '\\"'], $accessFormatJson);
$wwwConfOri  = __DIR__ . '/../etc/php-fpm.d/www.conf.stub.conf';
$wwwConfDest = __DIR__ . '/../etc/php-fpm.d/www.conf';

$content = file_get_contents($wwwConfOri);
$content = preg_replace('/^(access\.format).*$/m', sprintf('$1 = "%s"', $format), $content);
file_put_contents($wwwConfDest, $content);

// Check that the destination file has been written
if (!file_exists($wwwConfDest) || strpos(file_get_contents($wwwConfDest), $format) === false) {
    echo "Error while patching access.format in $wwwConfDest\n";
    exit(1);
}

echo "Successfully patched access.format in $wwwConfDest\n";
