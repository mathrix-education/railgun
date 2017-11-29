<?php
use App\Models\Module;

/**
 * ModuleMatchFactory
 *
 * @author Mathrix Team
 * @var Illuminate\Database\Eloquent\Factory $factory
 */
$factory->defineAs(Module::class, 'match', function () use ($factory) {
    $data = $factory->rawOf(Module::class, 'mcq');

    foreach($data['corrections'] as $k => $v) {
        $data['corrections'][$k] = $v[0];
    }

    $data['type'] = 'match';

    return $data;
});