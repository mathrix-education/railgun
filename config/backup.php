<?php
return [
    "mysql" => [
        "mysql_path" => "mysql",
        "mysqldump_path" => "mysqldump",
        "compress" => true,
        "local-storage" => [
            "disk" => "storage",
            "path" => "backups",
        ],

        "cloud-storage" => [
            "enabled" => false,
            "disk" => "drive",
            "path" => "db_backups",
            "keep-local" => true,
        ]
    ]
];
