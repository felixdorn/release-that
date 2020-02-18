<?php

namespace App\Commands;

use App\Actions\Committing;
use App\Actions\Pushing;
use App\Actions\Tagging;
use App\App;
use App\Config\Configuration;
use App\Version\VersionManager;
use GitElephant\Repository;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;
use PHLAK\SemVer\Version as VersionHolder;

class ReleaseCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run
        {--no-hooks : Disable all hooks}
        {--no-hook= : Disable hook(s). Hooks name are comma separated.}
        {--dry-run : Enable dry-run mode}
        {--patch : Bump to patch version}
        {--minor : Bump to minor version}
        {--major : Bump to major version}
        {--custom : Bump to custom version}
        {--config= : Custom config filename}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Start the releasing process';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        app()->singleton(
            'input',
            (function () {
                return $this->input;
            })->bindTo($this)
        );

        app()->singleton(
            'output',
            (function () {
                return $this->output;
            })
        );

        app()->singleton(
            'config',
            function () {
                return (new Configuration())->retrieve();
            }
        );

        app()->singleton(
            'git',
            function () {
                return Repository::open(App::cwd());
            }
        );

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Running in dry-run mode.');
        }

        App::events()->emit('beforeAll');

        $this->output->write(
            <<<ASCII

 _____      _                     _   _           _
|  __ \    | |                   | | | |         | |
| |__) |___| | ___  __ _ ___  ___| |_| |__   __ _| |_
|  _  // _ | |/ _ \/ _` / __|/ _ | __| '_ \ / _` | __|
| | \ |  __| |  __| (_| \__ |  __| |_| | | | (_| | |_
|_|  \_\___|_|\___|\__,_|___/\___|\__|_| |_|\__,_|\__|
ASCII
        );

        if (!File::exists(App::cwd('.git'))) {
            $this->error('Not a git repository');
            die(1);
        }

        App::events()->emit('beforeRelease');

        $versionManager = new VersionManager();

        $version = false;

        if ($this->option('minor')) {
            $version = $versionManager->nextMinor();
        }

        if ($this->option('major')) {
            $version = $versionManager->nextMajor();
        }

        if ($this->option('patch')) {
            $version = $versionManager->nextPatch();
        }

        if ($this->option('custom')) {
            $version = new VersionHolder($this->option('custom'));
        }

        if ($version === false) {
            $version = $this->choice(
                'Choose the version',
                [
                    sprintf('major (%s)', $versionManager->nextMajor()),
                    sprintf('minor (%s)', $versionManager->nextMinor()),
                    sprintf('patch (%s)', $versionManager->nextPatch()),
                    'custom'
                ],
                sprintf('minor (%s)', $versionManager->nextMinor())
            );

            $version = $versionManager->fromConsoleInput($version);
        }

        (new Committing(
            App::input(),
            App::output(),
            App::config()
        ))->do($versionManager, $version);

        (new Tagging(
            App::input(),
            App::output(),
            App::config()
        ))->do($versionManager, $version);

        (new Pushing(
            App::input(),
            App::output(),
            App::config()
        ))->do($versionManager, $version);


        $this->comment(
            sprintf(
                'Released in %ss',
                round(
                    microtime(true) - LARAVEL_START,
                    3
                )
            )
        );

        App::events()->emit('afterAll');

        if ($isDryRun) {
            $this->output->newLine();
            $this->warn('End dry-run. Enjoyed?');
        }

        exit(0);
    }
}
