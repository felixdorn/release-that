<?php
declare(strict_types=1);

namespace App\Version;

use App\App;
use PHLAK\SemVer\Version as VersionHolder;

class Placeholder
{
    /**
     * @param string $message
     * @param VersionHolder $version
     * @return string
     */
    public static function remove(string $message, VersionHolder $version): string
    {
        $remote = App::config('push.remote');

        $placeholders = [
            'version' => $version->__toString(),
            'date' => date('Y-m-d'),

            'newFilesCount' => App::git()->getWorkingTreeStatus()->all()->count(),
            'repo.remote' => $remote  ? App::git()->getRemote($remote)->getName() : '(no remote set)',
            'repo.pushUrl' => $remote   ? App::git()->getRemote($remote)->getPushURL() : '(no remote set)',
            'repo.fetchUrl' => $remote ? App::git()->getRemote($remote)->getFetchURL() : '(no remote set)',
        ];

        foreach ($placeholders as $key => $value) {
            $message = str_replace(
                sprintf('{%s}', $key),
                $value,
                $message
            );
        }

        return $message;
    }
}
