<?php


namespace Tests\Feature;

use Tests\TestCase;

class ReleaseCommandTest extends TestCase
{
    public function testReleaseCommand()
    {
        $this->artisan('init');

        $this
            ->artisan('run --dry-run')
            ->expectsQuestion('Choose the version', 'minor (0.1.0)')
            ->expectsQuestion('Commit `chore: release 0.1.0`', true)
            ->expectsQuestion('Tag release with `0.1.0`', true);
    }
}
