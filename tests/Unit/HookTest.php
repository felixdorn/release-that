<?php


namespace Tests\Unit;


use App\Events\Hook;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HookTest extends TestCase
{

    public function testHook()
    {
        $this->assertFileNotExists('tmp-test.release.txt');

        $hook = new Hook('touch tmp-test.release.txt', 'theName');
        $hook->run();

        $this->assertFileExists('tmp-test.release.txt');
        $this->assertTrue($hook->shouldRun());
        File::delete('tmp-test.release.txt');
    }

}
