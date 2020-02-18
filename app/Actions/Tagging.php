<?php
declare(strict_types=1);

namespace App\Actions;

use App\App;
use App\Version\VersionManager;
use PHLAK\SemVer\Version;

class Tagging extends AbstractAction
{
    /**
     * @inheritDoc
     */
    public function do(VersionManager $manager, Version $version)
    {
        $shouldTag = $this->output->confirm(
            sprintf('Tag release with `%s`', $manager->getTag($version)),
            App::config('tag') ? true : false
        );

        if ($shouldTag) {
            App::events()->emit('beforeTag');
            $tagName = $manager->getTag($version);
            if (!$this->isDryRun()) {
                App::git()->createTag(
                    $tagName,
                    null,
                    $manager->getTagMessage($version)
                );
            }
            $this->output->write(
                sprintf(
                    'Tagged release with `%s`%s',
                    $tagName,
                    PHP_EOL
                )
            );

            App::events()->emit('afterTag');
        }
    }
}
