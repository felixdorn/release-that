<?php


namespace App;

use GitElephant\Repository;
use PHLAK\SemVer\Version as VersionHolder;

class Version
{

    public function fromConsoleInput(string $version)
    {
        if ($version !== 'custom') {
            $version = trim(explode(' ', $version)[1], '()');

            return new VersionHolder($version);
        }

        return new VersionHolder(
            Application::output()->ask('Enter the custom version (must follow semver)')
        );
    }

    public function nextMajor()
    {
        return $this->currentTag()->incrementMajor();
    }

    private function currentTag(): VersionHolder
    {
        /** @var Repository $git */
        $git = resolve('git');

        $tags = $git->getTags();


        return new VersionHolder(
            $tags === [] ? '0.0.0' : $tags[array_key_last($tags)]->getName()
        );
    }

    public function nextMinor()
    {
        return $this->currentTag()->incrementMinor();
    }

    public function nextPatch()
    {
        return $this->currentTag()->incrementPatch();
    }

    public function getCommit(VersionHolder $version)
    {
        return Placeholder::remove((
            Application::config()['commit']['message'],
            $version
        );
    }


    public function getTag(VersionHolder $version)
    {
        return Placeholder::remove(
            Application::config()['tag']['name'],
            $version
        );
    }

    public function getTagMessage(VersionHolder $version)
    {

        return Placeholder::remove((
            Application::config()['tag']['message'],
            $version
        );
    }
}
