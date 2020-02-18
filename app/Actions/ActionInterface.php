<?php

namespace App\Actions;

use App\Version\VersionManager;
use PHLAK\SemVer\Version;

interface ActionInterface
{
    /**
     * @param  VersionManager $manager
     * @param  Version        $version
     * @return mixed
     */
    public function do(VersionManager $manager, Version $version);
}
