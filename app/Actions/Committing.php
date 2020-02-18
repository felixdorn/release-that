<?php
declare(strict_types=1);

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
            $commitMessage = $manager->getCommit($version);

            if (!$this->isDryRun()) {
                App::git()->commit(
                    $commitMessage,
                    App::config('commit.stageAll')
                );
            }

            $this->output->write(
                sprintf(
                    'Committed `%s`%s',
                    $commitMessage,
                    PHP_EOL
                )
            );

            App::events()->emit('afterCommit');
        }
    }
}
