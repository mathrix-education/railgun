<?php

namespace App\Console\Commands\Pipelines;

/**
 * Class DeployCommand.
 * Allow an easy deployment procedure.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Pipelines
 */
class DeployCommand extends PipelineCommand
{
    use InteractsWithBuild, InteractsWithServer;

    protected $signature = "pipelines:deploy {--server=} {--environment=} {--buildName=}";


    public function handle()
    {
        $this->setupEnvironment();
        $this->selectBuild();
        $this->setupServer();
        $this->setupVars();

        $this->buildIfNeeded();
        $this->init();
        $this->connect();
        $this->send();
        $this->setup();
    }


    /**
     * Build if needed
     */
    public function buildIfNeeded()
    {
        if (!$this->buildExists()) {
            $shouldBuild = $this->confirm("Build $this->buildName does not exists. Do you want to create it now?", true);

            if ($shouldBuild) {
                $this->call("pipelines:build", [
                    "--environment" => $this->environment,
                    "--buildName" => $this->buildName
                ]);
            } else {
                $this->abort("Build declined, aborting.");
            }
        }
    }


    /**
     * Initialize environment and server
     */
    private function init()
    {
        $this->block("Starting deployment of {$this->environment} on {$this->server}...", "info");

        // Ask for user confirmation
        $this->line(" Deployment summary");
        $this->line(" Server     : $this->server");
        $this->line(" Environment: $this->environment");
        $this->line(" Host       : $this->host:$this->port");
        $this->line(" User       : $this->user");
        $this->line(" Release    : $this->buildName");
        $this->line("");

        $confirmation = $this->confirm("Are you sure you want to deploy now?");

        if (!$confirmation && !$this->option("no-interaction")) {
            $this->abort("Aborting.");
        }

        // Secure deployment on web01/master
        if ($this->server === "web01" && $this->environment === "master" && !$this->option("no-interaction")) {
            $deploy = $this->confirm(
                "You choose to deploy master environment on web01 server, which is the production. Are you sure?"
            );

            if (!$deploy) {
                $this->abort("Aborting");
            }
        }
    }


    /**
     * Send the build.tar.gz over SCP to the remote server
     */
    private function send()
    {
        $this->line("Sending $this->buildFileName over SCP...", null, "v");
        $sendResult = ssh2_scp_send($this->session, $this->buildFileName, $this->distantBuildFileName, 0600);

        if ($sendResult) {
            $this->success("Sent $this->buildFileName over SCP");
        } else {
            $this->abort("Error while sending $this->buildFileName over SCP.");
        }
    }


    /**
     * Extract and setup the new release
     */
    private function setup()
    {
        // Setup environment
        $this->exec("mkdir -p " . implode(" ", [
                $this->releasesDirectory,
                $this->sharedDirectory,
                "$this->sharedDirectory/backups/cdn",
                "$this->sharedDirectory/backups/database",
                "$this->sharedDirectory/framework/cache",
                "$this->sharedDirectory/framework/views",
                "$this->sharedDirectory/logs",
                "$this->sharedDirectory/sitemaps",
                "$this->sharedDirectory/cdn"
            ])
        );
        $this->success("Working directory $this->nextReleaseDirectory initialized");

        // composer install
        $this->exec("unzip -o $this->distantBuildFileName -d $this->nextReleaseDirectory");
        $this->exec("composer install -d $this->nextReleaseDirectory");
        $this->success("composer install");

        // php artisan migrate
        $this->exec("php $this->nextReleaseDirectory/artisan migrate");
        $this->success("Database migrated");

        // Linking
        $this->exec("cp -R $this->nextReleaseDirectory/storage/* $this->sharedDirectory");
        $this->exec("rm -rf $this->nextReleaseDirectory/storage");
        $this->exec("ln -nfs $this->sharedDirectory/storage/ $this->nextReleaseDirectory/storage");
        $this->success("Shared directories linked");

        $this->exec("rm -f $this->currentSymlink");
        $this->exec("ln -nfs $this->nextReleaseDirectory $this->currentSymlink");
        $this->success("Current symlink changed to $this->currentSymlink");

        // Clean
        $this->exec("rm $this->distantBuildFileName");
        $this->exec("ls -dt $this->releasesDirectory/*/ | tail -n +6 | xargs rm -rf");
        $this->success("Build cleaned");

        $this->success("$this->buildName successfully deployed!");
    }
}
