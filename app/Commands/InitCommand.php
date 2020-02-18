<?php

namespace App\Commands;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init {name=.release-that.json}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new configuration file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $configPath = getcwd() . "/{$this->argument('name')}";

        if ($this->output->isVerbose()) {
            $isWritable = File::isWritable(dirname($configPath));
            $exists = File::exists($configPath);

            $this->output->write([
                "Config file will be created at{$configPath}" . PHP_EOL,
                'Parent directory is ' . ($isWritable ? 'writable' : 'not writable') . PHP_EOL,
                $exists ? 'Configuration file already exists' : '' . PHP_EOL,
                'Directory permission:' .substr(sprintf('%o', fileperms(dirname($configPath))), -4) . PHP_EOL
            ]);

            $this->confirm('Continue?');
        }

        $created = File::put(
            $configPath,
            File::get(
                base_path('stubs/') . '.release.json'
            )
        );

        if ($created) {
            $this->output->success('Created configuration file');
        } else {
            $this->output->error('Can not create configuration file');
            exit(1);
        }

        exit(0);
    }
}
