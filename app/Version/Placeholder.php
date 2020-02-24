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

        if ($remote) {
            $gitRemote = App::git()->getRemote($remote);
            $placeholders = [
                'repo.remote' => $gitRemote->getName(),
                'repo.pushUrl' => $gitRemote->getPushURL(),
                'repo.fetchUrl' => $gitRemote->getFetchURL(),
            ];
        }

        $placeholders['version'] = (string)$version;
        $placeholders['date'] = date('Y-m-d');

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
