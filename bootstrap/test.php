<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

putenv('APP_ENV=dev');
(new App\Services\GoogleCloud\KMS\SetupEnvironment(dirname(__DIR__)))->bootstrap();
