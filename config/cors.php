<?php

return [
    "supportsCredentials" => true,
    "allowedOrigins" => ["localhost:4200", "*.mathrix.fr", "mathrix.fr"],
    "allowedHeaders" => ["*"],
    "allowedMethods" => ["*"],
    "exposedHeaders" => [],
    "maxAge" => 3600 * 24 * 14,
    "hosts" => [],
];
