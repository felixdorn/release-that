<?php
declare(strict_types=1);

namespace App\Version;

use App\App;
use GitElephant\Repository;
use PHLAK\SemVer\Version as VersionHolder;

class VersionManager
{
    /**
     * @param  string $version
     * @return VersionHolder
     */
    public function fromConsoleInput(string $version): VersionHolder
    {
        if ($version !== 'custom') {
            $version = trim(explode(' ', $version)[1], '()');

            return new VersionHolder($version);
        }

        $version = App::output()->ask('Enter the custom version (must follow semver)');
        return new VersionHolder($version);
    }

    /**
     * @return VersionHolder
     */
    public function nextMajor(): VersionHolder
    {
        return $this->currentTag()->incrementMajor();
    }

    /**
     * @return VersionHolder
     */
    private function currentTag(): VersionHolder
    {
        /**
         * @var Repository $git
         */
        $git = resolve('git');

        $tags = $git->getTags();


        return new VersionHolder(
            $tags === [] ? '0.0.0' : $tags[array_key_last($tags)]->getName()
        );
    }

    /**
     * @return VersionHolder
     */
    public function nextMinor(): VersionHolder
    {
        return $this->currentTag()->incrementMinor();
    }

    /**
     * @return VersionHolder
     */
    public function nextPatch(): VersionHolder
    {
        return $this->currentTag()->incrementPatch();
    }

    /**
     * @param  VersionHolder $version
     * @return string
     */
    public function getCommit(VersionHolder $version): string
    {
        return Placeholder::remove(
            App::config('commit.message'),
            $version
        );
    }

    /**
     * @param  VersionHolder $version
     * @return string
     */
    public function getTag(VersionHolder $version): string
    {
        return Placeholder::remove(
            App::config('tag.name'),
            $version
        );
    }

    /**
     * @param  VersionHolder $version
     * @return string
     */
    public function getTagMessage(VersionHolder $version): string
    {
        return Placeholder::remove(
            App::config('tag.message'),
            $version
        );
    }
}
