<?php

return [
    "mysql" => [
        "mysql_path" => "mysql",
        "mysqldump_path" => "mysqldump",
        "compress" => true,
        "local-storage" => [
            "disk" => "storage",
            "path" => "backups/database",
        ],
        "cloud-storage" => [
            "enabled" => true,
            "disk" => "drive",
            "path" => "1ep0UU49iVvWRh0QdZ-E7gbwPBIInJmqj",
            "keep-local" => true,
        ],
    ],
];
