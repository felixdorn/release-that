<?php


namespace App\Config;


use GitElephant\Repository;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\File;
use Nette\Schema\Processor;
use Suin\Json;

class Configuration
{
    public const FILENAMES = [
        '.release.json',
        '.release-that',
        '.release-that.json',
        'iMs0D4rK.release.json',
    ];


    public static function create(): array
    {
        $cwd = resolve('path');
        $config = false;

        foreach (self::FILENAMES as $filename) {
            if (File::exists($cwd . $filename)) {
                $config = File::get($filename);
            }
        }

        if (!$config) {
            app('output')->error('No configuration file found.');
            die(1);
        }

        $config = Json::decode($config, true);

        $config = (new Processor())->process(
            Schema::getSchema(),
            $config
        );

        $config = Json::decode(Json::encode($config), true);

        foreach ($config['tasks'] as $index => $task) {
            if (is_string($task)) {
                $config['tasks'][$index] = [$task];
            }
        }


        return $config;
    }

}
