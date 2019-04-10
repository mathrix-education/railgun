<?php

use App\Models\Module;
use Illuminate\Database\Eloquent\Factory;

/**
 * ModuleFlashFactory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 *
 * @var Factory $factory
 */
$factory->defineAs(Module::class, "flashcard", function () use ($factory) {
    $data = $factory->rawOf(Module::class, "mcq");

    $data["type"] = "flashcard";
    $data["choices"] = null;
    $data["solutions"] = null;

    foreach ($data["questions"] as $k => $v) {
        $data["questions"][$k] = $v[0];
    }
    foreach ($data["corrections"] as $k => $v) {
        $data["corrections"][$k] = $v[0];
    }

    return $data;
});
