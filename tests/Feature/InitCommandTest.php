<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InitCommandTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testInitCommand()
    {
        $this->artisan('init .release.test.json')->assertExitCode(0);

        $this->assertFileExists(
            '.release.test.json'
        );

        $this->assertEquals(
            File::get(base_path('stubs/.release.json')),
            File::get('.release.test.json')
        );

        File::delete('.release.test.json');
    }
}
