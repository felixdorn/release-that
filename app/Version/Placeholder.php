<?php

namespace App\Version;

use App\App;
use PHLAK\SemVer\Version as VersionHolder;

class Placeholder
{
    /**
     * @param  string        $message
     * @param  VersionHolder $version
     * @return string
     */
    public static function remove(string $message, VersionHolder $version): string
    {


        $placeholders = [
            'version' => $version->__toString(),
            'date' => date('Y-m-d'),
            'newFilesCount' => App::git()->getWorkingTreeStatus()->all()->count(),
            'repo.remote' => App::git()->getRemote(
                App::config('push.remote') ?? '(no remote set)'
            )->getName(),
            'repo.pushUrl' => App::git()->getRemote(
                App::config('push.remote') ?? '(no remote set)'
            )->getPushURL(),
            'repo.fetchUrl' => App::git()->getRemote(
                App::config('push.remote') ?? '(no remote set)'
            )->getFetchURL(),
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
