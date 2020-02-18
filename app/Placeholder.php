<?php


namespace App;


use PHLAK\SemVer\Version as VersionHolder;

class Placeholder
{
    /**
     * @param string $message
     * @param VersionHolder $version
     * @return string|string[]
     */
    public static function remove(string $message, VersionHolder $version)
    {
        $placeholders = [
            'version' => $version->__toString(),
            'date' => date('Y-m-d'),
            'newFilesCount' => Application::git()->getWorkingTreeStatus()->all()->count(),
            'repo.remote' => Application::git()->getRemote(
                Application::config()['push']['remote']
            )->getName(),
            'repo.pushUrl' => Application::git()->getRemote(
                Application::config()['push']['remote']
            )->getPushURL(),
            'repo.fetchUrl' => Application::git()->getRemote(
                Application::config()['push']['remote']
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
