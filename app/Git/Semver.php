<?php


namespace App\Git;


use GitElephant\Repository;
use PHLAK\SemVer\Version;

class Semver
{

    public static function getVersionFromConsoleChoice(string $version, bool $isCustom)
    {
        if (!$isCustom) {
            $version = trim(explode(' ', $version)[1], '()');
        }

        return new Version($version);
    }

    public function nextMajor()
    {
        return $this->currentTag()->incrementMajor();
    }

    private function currentTag(): Version
    {
        /** @var Repository $git */
        $git = resolve('git');

        $tags = $git->getTags();


        return new Version(
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

}
