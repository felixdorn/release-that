<?php

namespace App\Actions;

use App\App;
use App\Version\VersionManager;
use PHLAK\SemVer\Version;

class Committing extends AbstractAction
{
    public function do(VersionManager $manager, Version $version)
    {
        $shouldCommit = $this->output->confirm(
            sprintf('Commit `%s`', $manager->getCommit($version)),
            App::config('commit') ? true : false
        );


        if ($shouldCommit) {
            App::events()->emit('beforeCommit');
            $unstagedFiles = App::git()->getWorkingTreeStatus()->all();
            $commitMessage = $manager->getCommit($version);

            if (!$this->isDryRun()) {
                App::git()->commit(
                    $commitMessage,
                    App::config('commit.stageAll')
                );
            }

            $this->output->write(
                sprintf(
                    'Committed %s files/directories with message `%s`%s',
                    $unstagedFiles->count(),
                    $commitMessage,
                    PHP_EOL
                )
            );

            App::events()->emit('afterCommit');
        }
    }
}
