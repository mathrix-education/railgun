<?php
// APP_BUILD
$build = "unknown";

if (file_exists(__DIR__ . "/../build.txt")) {
    $build = trim(file_get_contents(__DIR__ . "/../build.txt"));
} elseif (`which git`) {
    $gitSha1 = exec("git log --pretty=format:'%H' -n 1");
    $build = substr($gitSha1, 0, 7);
}

putenv("APP_BUILD=$build");
