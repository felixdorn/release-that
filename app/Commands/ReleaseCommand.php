<?php

namespace App\Commands;

use App\Config\Configuration;
use App\Config\Events;
use App\Git\Semver;
use GitElephant\Repository;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class ReleaseCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'release';

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
        app()->singleton('input', fn() => $this->input);
        app()->singleton('output', fn() => $this->output);
        app()->singleton('path', fn() => getcwd() . '/');
        app()->singleton('config', fn() => Configuration::create());
        app()->singleton('git', fn() => Repository::open(resolve('path')));
        Events::emit('beforeAll');

        $config = resolve('config');

        $this->output->title("Let's release that");

        $this->info('Resolved configuration.');
        $this->output->newLine();
        $this->line('Commit: ' . ($config['commit'] !== false ? 'yes' : 'no'),);
        $this->line('Tag: ' . ($config['tag'] !== false ? 'yes' : 'no'));
        $this->line('Push: ' . ($config['push'] !== false ? 'yes' : 'no'));

        if (!File::exists(resolve('path') . '.git')) {
            resolve('output')->error('Not a git repository');
            die(1);
        }

        $semver = new Semver();

        /** @var Repository $git */
        $git = resolve('git');

        Events::emit('beforeRelease');

        $version = $this->choice('Choose the version', [
            "major ({$semver->nextMajor()})",
            "minor ({$semver->nextMinor()})",
            "patch ({$semver->nextPatch()})",
            "custom"
        ], "minor ({$semver->nextMinor()})");
        $isCustom = false;

        if ($version === 'custom') {
            $isCustom = true;
            $version = $this->ask('Enter the custom version (must follow semver)');
        }

        $version = Semver::getVersionFromConsoleChoice($version, $isCustom);

        if ($config['commit']) {

            Events::emit('beforeCommit');

            $unstagedFiles = $git->getWorkingTreeStatus()->all();

            $git->commit(
                $commitMessage = Str::replaceFirst('{version}', $version, $config['commit']['message']),
                $config['commit']['stageAll'],
                null,
                null,
                $config['commit']['empty']
            );

            $this->line("Committed {$unstagedFiles->count()} files/directories with message `{$commitMessage}`.");

            Events::emit('afterCommit');
        }

        if ($config['tag']) {
            Events::emit('beforeTag');

            $git->createTag(
                $tagName = Str::replaceFirst('{version}', $version, $config['tag']['name']),
                null,
                Str::replaceFirst('{version}', $version, $config['tag']['message'])
            );

            $this->info("Tagged {$tagName}.");

            Events::emit('afterTag');
        }

        if ($config['push']) {
            Events::emit('beforePush');

            $git->push(
                $remote = $config['push']['remote'],
                null,
                $config['push']['arguments']
            );

            $this->line("Pushed to $remote");

            Events::emit('afterPush');
        }


        $this->output->newLine();
        $this->comment("Released in " . round(microtime(true) - LARAVEL_START, 3) . 's');

        Events::emit('afterRelease', 'afterAll');
    }

}
