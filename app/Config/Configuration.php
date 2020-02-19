<?php
declare(strict_types=1);

namespace App\Config;

use App\App;
use App\Events\Hook;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Nette\Schema\Processor;

class Configuration
{
    /**
     * @var string[]
     */
    private $filenames = [
        '.release.json',
        '.release-that', '.release-that.json',
    ];

    /**
     * @param string|null $configFile
     * @return array<string, array<int|string, string|bool>>
     */
    public function retrieve(?string $configFile = null): array
    {
        $cwd = App::cwd();
        $config = File::exists($cwd . $configFile) ? $cwd . $configFile : false;

        foreach ($this->filenames as $filename) {
            if (File::exists($cwd . $filename)) {
                $config = File::get($filename);
            }
        }

        if (!$config) {
            App::get()->output()->error('No configuration file found.');
            die(1);
        }

        (new Processor())->process(
            Schema::getSchema(),
            json_decode($config, true)
        );

        $config = json_decode($config, true);

        if (empty($config)) {
            App::get()->output()->error('No configuration file found.');
            die(1);
        }

        $baseConfig = json_decode(
            File::get(base_path('stubs/.release.json')),
            true
        );

        $config = array_merge(
            $baseConfig,
            $config
        );

        foreach ($config['hooks'] as $index => $task) {
            if (empty($task)) {
                continue;
            }

            if (is_string($task)) {
                $config['hooks'][$index] = [$task];
            }

            $hooked = [];
            foreach ($config['hooks'][$index] as $uniqueTask) {
                $hooked[] = new Hook($uniqueTask, $index);
            }

            $config['hooks'][$index] = $hooked;
        }

        return $config;
    }
}
