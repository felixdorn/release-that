<?php

namespace App\Commands;

use App\Application;
use App\Configuration;
use App\Version;
use GitElephant\Repository;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class ReleaseCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run
        {--no-hooks : Disable all hooks}
        {--no-hook= : Disable hook(s). Hooks name are comma separated. }
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
        app()->singleton('input', fn() => $this->input);
        app()->singleton('output', fn() => $this->output);
        app()->singleton('config', fn() => (new Configuration())->retrieve());
        app()->singleton('git', fn() => Repository::open(resolve('path')));

        Application::events()->emit('beforeAll');

        Application::output()->title(<<<ASCII
 _____      _                     _   _           _
|  __ \    | |                   | | | |         | |
| |__) |___| | ___  __ _ ___  ___| |_| |__   __ _| |_
|  _  // _ | |/ _ \/ _` / __|/ _ | __| '_ \ / _` | __|
| | \ |  __| |  __| (_| \__ |  __| |_| | | | (_| | |_
|_|  \_\___|_|\___|\__,_|___/\___|\__|_| |_|\__,_|\__|
ASCII
);

        if (!File::exists(Application::cwd() . '.git')) {
            Application::output()->error('Not a git repository');
            die(1);
        }

        Application::events()->emit('beforeRelease');

        $versionManager = new Version();

        $version = $this->choice('Choose the version', [
            sprintf('major (%s)', $versionManager->nextMajor()),
            sprintf('minor (%s)', $versionManager->nextMinor()),
            sprintf('patch (%s)', $versionManager->nextPatch()),
            'custom'
        ], sprintf('minor (%s)', $versionManager->nextMinor()));

        $version = $versionManager->fromConsoleInput($version);

        $shouldCommit = Application::output()->confirm(
            sprintf('Commit `%s`', $versionManager->getCommit($version)),
            Application::config()['commit'] !== false
        );

        if ($shouldCommit) {
            Application::events()->emit('beforeCommit');

            $unstagedFiles = Application::git()->getWorkingTreeStatus()->all();

            Application::git()->commit(
                $commitMessage = $versionManager->getCommit($version),
                Application::config()['commit']['stageAll']
            );

            $this->line(sprintf(
                'Committed %s files/directories with message `%s`%s',
                $unstagedFiles->count(),
                $commitMessage,
                PHP_EOL
            ));

            Application::events()->emit('afterCommit');
        }


        $shouldTag = Application::output()->confirm(
            sprintf('Tag release with `%s`', $versionManager->getTag($version)),
            Application::config()['tag'] !== false
        );

        if ($shouldTag) {
            Application::events()->emit('beforeTag');

            Application::git()->createTag(
                $tagName = $versionManager->getTag($version),
                null,
                $versionManager->getTagMessage($version)
            );

            $this->line(sprintf(
                'Tagged release with `%s`%s',
                $tagName,
                PHP_EOL
            ));

            Application::events()->emit('afterTag');
        }


        if (Application::config()['push']) {
            $remote = Application::git()->getRemote(
                Application::config()['push']['remote']
            );
        } else {
            $remote = false;
        }

        $shouldPush = Application::output()->confirm(
            sprintf('Push to %s (%s)', $remote ? $remote->getName() : 'no remote set', $remote ? $remote->getPushURL() : 'no push url set'),
            Application::config()['push'] !== false
        );

        if ($shouldPush) {
            Application::events()->emit('beforePush');

            Application::git()->push(
                $remote->getName(),
                null,
                Application::config()['push']['arguments']
            );
            $this->line(sprintf(
                'Pushed to %s%s',
                $remote->getName(),
                PHP_EOL
            ));

            Application::events()->emit('afterPush');
        }

        $this->comment(sprintf(
            'Released in %s',
            round(
                microtime(true) - LARAVEL_START,
                3
            )
        ));

        exit(0);
    }
}
