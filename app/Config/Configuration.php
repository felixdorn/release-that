<?php

namespace App\Config;

use App\App;
use App\Events\Hook;
use Illuminate\Support\Facades\File;
use Nette\Schema\Processor;
use Suin\Json;

class Configuration
{
    /**
     * @var string[]
     */
    private $filenames = [
        '.release.json',
        '.release-that',
        '.release-that.json',
    ];

    /**
     * @param  string|null $configFile
     * @return array<string, array<int|string, string|bool>>
     */
    public function retrieve(?string $configFile = null): array
    {
        $config = File::exists($configFile) ? $configFile : false;
        $cwd = App::cwd();


        foreach ($this->filenames as $filename) {
            if (File::exists($cwd . $filename)) {
                $config = File::get($filename);
            }
        }

        if (!$config) {
            App::get()->output()->error('No configuration file found.');
            die(1);
        }

        $config = (new Processor())->process(
            Schema::getSchema(),
            JSON::decode($config, true)
        );

        $config = Json::decode(Json::encode($config), true);

        foreach ($config['tasks'] as $index => $task) {
            if (empty($task)) {
                continue;
            }

            if (is_string($task)) {
                $config['tasks'][$index] = [$task];
            }

            $hookedTasks = [];
            foreach ($config['tasks'][$index] as $uniqueTask) {
                $hookedTasks[] = new Hook($uniqueTask, $index);
            }

            $config['tasks'][$index] = $hookedTasks;
        }

        return $config;
    }
}
