<?php
namespace App\Providers;

use App\Models\BaseModel;
use App\Models\Class_;
use App\Policies\BasePolicy;
use App\Policies\ClassPolicy;
use App\Policies\FilePolicy;
use App\Policies\NotificationPolicy;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Class PolicyServiceProvider.
 * Automatically register policies.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.1
 * @copyright Mathrix Education SA
 * @package   App\Providers
 */
class PolicyServiceProvider extends ServiceProvider
{
    private const MODELS_NAMESPACE = "App\\Models\\";
    private const POLICIES_NAMESPACE = "App\\Policies\\";
    private const EXCLUDED_POLICIES = [
        BasePolicy::class,
        ClassPolicy::class,
        NotificationPolicy::class,
        FilePolicy::class,
    ];

    /**
     * @throws \Exception
     */
    public function boot()
    {
        $policiesDir = $this->app->basePath() . "/app/Policies";
        $policyFiles = glob($policiesDir . \DIRECTORY_SEPARATOR . "*.php");

        foreach ($policyFiles as $policyFile) {
            $policyClass = self::POLICIES_NAMESPACE . mb_substr(basename($policyFile), 0, -4);

            if (\in_array($policyClass, self::EXCLUDED_POLICIES, true)) {
                continue;
            }

            /** @var BaseModel $modelClass */
            $modelClass = str_replace([self::POLICIES_NAMESPACE, "Policy"], [self::MODELS_NAMESPACE, ""],
                $policyClass);

            if (class_exists($modelClass)) {
                Gate::policy($modelClass, $policyClass);
            } else {
                throw new \Exception("$modelClass does not exist");
            }
        }

        Gate::policy(Class_::class, ClassPolicy::class);
        Gate::policy(FilePolicy::class, FilePolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
    }
}
