<?php
declare(strict_types=1);

namespace App\Events;

use App\App;

class Hook
{
    /**
     * @var string
     */
    private $command;
    /**
     * @var string|null
     */
    private $name;

    public function __construct(string $command, ?string $name = null)
    {
        $this->command = $command;
        $this->name = $name;
    }

    public function run(): int
    {
        if (empty($this->command)) {
            return 0;
        }

        $git = App::git();
        $command = str_replace('{repo.remote}', $remote = App::config('push.remote'), $this->command);
        $command = str_replace('{repo.pushUrl}', $git->getRemote($remote)->getPushURL(), $command);
        $command = str_replace('{repo.fetchUrl}', $git->getRemote($remote)->getFetchURL(), $command);
        $command = str_replace('{version}', App::version(), $command);

        $result = exec(sprintf('%s 2>&1', $command), $output, $ret);

        App::output()->write($result);

        if ($ret !== 0) {
            App::output()->error(
                sprintf('Task %sfailed with exit-code %s', $this->name !== null ? $this->name . ' ' : '', $ret)
            );

            exit($ret);
        }

        return 0;
    }

    public function shouldRun(): bool
    {
        if (App::input()->getOption('no-hooks')) {
            return false;
        }

        if (App::input()->getOption('no-hook')) {
            $disabledHooks = App::input()->getOption('no-hook');

            if (!is_string($disabledHooks)) {
                return true;
            }

            $disabledHooks = explode(',', $disabledHooks);

            foreach ($disabledHooks as $disabledHook) {
                if ($this->name === $disabledHook) {
                    return false;
                }
            }
        }

        return true;
    }
}
