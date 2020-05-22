#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

while (!feof(STDIN)) {
    $lines = fgets(STDIN);

    if (!is_string($lines)) {
        continue;
    }

    $lines = explode("\n", $lines);

    foreach ($lines as $line) {
        $parsed = parse($line);

        if ($parsed) {
            echo $parsed . "\n";
        }
    }
}

function parse(string $line): ?string
{
    if ($line === '') {
        return null;
    }

    if (is_json($line)) {
        return $line;
    }

    $level = 'INFO';
    $line  = trim($line, " \t\n\r\0\x0B\n");

    $ignorePatterns = [
        '/^Read configuration/',
        '/^' . preg_quote(getcwd(), '/') . '/',
        '/^\|\s+[a-zA-Z-_]+\s+\|/',
        '/^\+[-+]+\+$/',
        '/^--- Worker \d+ stderr ---$/',
    ];

    $replacePatterns = [
        '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]\[\w+\] /' => '$1', // Worker logs
        '/Failed:\s+(.*)/'                                   => 'Failed: $1',
    ];

    $levelPatterns = [
        'ERROR' => [
            '/^Failed:/',
        ],
    ];

    foreach ($replacePatterns as $pattern => $replacement) {
        $line = preg_replace($pattern, $replacement, $line);
    }

    foreach ($ignorePatterns as $pattern) {
        if (preg_match($pattern, $line)) {
            return null;
        }
    }

    foreach ($levelPatterns as $theLevel => $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                $level = $theLevel;
            }
        }
    }

    return sprintf('{"severity":"%s","message":"%s"}', $level, $line);
}
