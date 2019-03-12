<?php

namespace App\Console\Commands\Pipelines;

/**
 * Trait InteractsWithServer.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Pipelines
 * @mixin     PipelineCommand
 */
trait InteractsWithServer
{
    // Server and SSH configuration
    protected $server;
    protected $environment;
    protected $host;
    protected $port;
    protected $user;
    protected $publicKey;
    protected $privateKey;
    protected $session;
    protected $shell;
    // Server variables
    protected $apiDirectory;
    protected $currentSymlink;
    protected $releasesDirectory;
    protected $sharedDirectory;
    protected $nextReleaseDirectory;
    protected $distantBuildFileName;


    /**
     * Setup server, asking for environment and server.
     */
    public function setupServer()
    {
        $servers = json_decode(file_get_contents(app()->basePath("servers.json")), true);

        $this->server = $this->option("server");

        if ($this->server == null || $this->environment == null) {
            // Choose server if not given in options
            $this->server = $this->choice("Choose a server", array_keys($servers), $this->server);

            // Choose environment if not given in options
            $this->environment = $this->choice("Choose an environment", $servers[$this->server]["environments"]);
        }

        $this->host = $servers[$this->server]["host"];
        $this->port = $servers[$this->server]["port"];
        $this->user = $servers[$this->server]["user"];

        // Check that server exists
        if (!in_array($this->server, array_keys($servers))) {
            $this->warn("Unknown server: {$this->server}, aborting.");
            exit(1);
        }

        if (!in_array($this->environment, $servers[$this->server]["environments"])) {
            $this->warn("Environment {$this->environment} is not allowed on server {$this->server}, aborting.");
            exit(1);
        }
    }


    /**
     * Execute a command on the distant server.
     *
     * @param string $command
     */
    public function exec(string $command)
    {
        $this->line("<info>$</info> $command", null, "v");
        $stream = ssh2_exec($this->session, $command);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        // Enable blocking for both streams
        stream_set_blocking($errorStream, true);
        stream_set_blocking($stream, true);

        while ($o = fgets($stream)) {

            if ($this->getOutput()->isVerbose()) {
                $lines = explode("\n", $o);

                foreach ($lines as $line) {
                    if (empty($line)) {
                        continue;
                    }

                    $this->line(rtrim($line));
                }
            }

            usleep(100);
        }

        while ($o = fgets($errorStream)) {
            if ($this->getOutput()->isVerbose()) {
                $lines = explode("\n", $o);
                foreach ($lines as $line) {
                    if (empty($line)) {
                        continue;
                    }

                    $this->line(rtrim($line));
                }
            }

            usleep(100);
        }

        // Close the streams
        fclose($errorStream);
        fclose($stream);
    }


    /**
     * Setup distant server variables.
     */
    private function setupVars()
    {
        // Server variables
        $this->apiDirectory = "/var/www/{$this->environment}/api";
        $this->currentSymlink = "$this->apiDirectory/current";
        $this->releasesDirectory = "$this->apiDirectory/releases";
        $this->sharedDirectory = "$this->apiDirectory/shared";
        $this->nextReleaseDirectory = "$this->releasesDirectory/$this->buildName";
        $this->distantBuildFileName = "$this->releasesDirectory/$this->buildName.zip";
    }


    /**
     * SSH connection via public/private key files
     */
    private function connect()
    {
        $this->line("Establishing secure SSH connection with SSH keys as $this->user@$this->host:$this->port", null, "v");

        // Load keys
        $this->publicKey = app()->basePath("deploy/keys/$this->server/id_rsa.pub");
        $this->privateKey = app()->basePath("deploy/keys/$this->server/id_rsa");

        if (!file_exists($this->publicKey)) {
            $this->error("$this->publicKey does not exists, aborting.");
        }
        if (!file_exists($this->privateKey)) {
            $this->error("$this->privateKey does not exists, aborting.");
        }

        $this->session = ssh2_connect($this->host, $this->port);
        $result = ssh2_auth_pubkey_file($this->session, $this->user, $this->publicKey, $this->privateKey);

        if ($result) {
            $this->success("Logged as $this->user@$this->host:$this->port");
            $this->shell = ssh2_shell($this->session);
        } else {
            $this->abort("Authentication failed.");
        }
    }
}
