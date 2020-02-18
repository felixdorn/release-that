<?php
declare(strict_types=1);

namespace App\Commands;

use App\App;
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
        $configPath = App::cwd($this->argument('name'));

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
    }
}
