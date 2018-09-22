<?php

namespace App\Providers;

use App\Models\BaseModel;
use App\Models\Class_;
use App\Observers\BaseObserver;
use App\Observers\ClassObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Class ObserverServiceProvider
 * Provides all observers to the corresponding models.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class ObserverServiceProvider extends ServiceProvider
{
    private const MODELS_NAMESPACE = "App\\Models\\";
    private const OBSERVERS_NAMESPACE = "App\\Observers\\";
    private const EXCLUDED_OBSERVERS = [
        BaseObserver::class,
        ClassObserver::class,
    ];

    /**
     * Auto-discover observers based on the filename.
     *
     * @throws \Exception
     */
    public function boot()
    {
        $observersDir = $this->app->basePath() . "/app/Observers";
        $observerFiles = glob($observersDir . \DIRECTORY_SEPARATOR . "*.php");

        foreach ($observerFiles as $observerFile) {
            $observerClass = self::OBSERVERS_NAMESPACE . mb_substr(basename($observerFile), 0, -4);

            if (\in_array($observerClass, self::EXCLUDED_OBSERVERS, true)) {
                continue;
            }

            /** @var BaseModel $modelClass */
            $modelClass = str_replace([self::OBSERVERS_NAMESPACE, "Observer"], [self::MODELS_NAMESPACE, ""],
                $observerClass);

            if (class_exists($modelClass)) {
                $modelClass::observe($observerClass);
            } else {
                throw new \Exception("$modelClass does not exist");
            }
        }

        Class_::observe(ClassObserver::class);
    }
}
