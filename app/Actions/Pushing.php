<?php
declare(strict_types=1);

namespace App\Actions;

use App\App;
use App\Version\VersionManager;
use PHLAK\SemVer\Version;

class Pushing extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function do(VersionManager $manager, Version $version)
    {
        if (App::config('push')) {
            $remote = App::git()->getRemote(
                App::config('push.remote')
            );
        } else {
            $remote = false;
        }


        if ($remote === false) {
            return;
        }

        $shouldPush = $this->output->confirm(
            sprintf(
                'Push to %s',
                $remote->getName()
            ),
            App::config('push') !== false
        );

        if ($shouldPush) {
            App::events()->emit('beforePush');
            if (!$this->isDryRun()) {
                App::git()->push(
                    $remote->getName(),
                    null,
                    App::config('push.arguments')
                );
            }
            $this->output->write(
                sprintf(
                    'Pushed to %s%s',
                    $remote->getName(),
                    PHP_EOL
                )
            );
            App::events()->emit('afterPush');
        }
    }
}
