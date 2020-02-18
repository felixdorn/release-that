<?php


namespace App;

class Hook
{
    /**
     * @var string
     */
    private string $command;
    /**
     * @var string
     */
    private ?string $name;

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

        Application::output()->write(
            exec(sprintf('%s 2>&1', $this->command), $output, $ret)
        );


        if ($ret !== 0) {
            Application::output()->error(
                sprintf('Task %sfailed with exit-code %s', $this->name !== null ? $this->name . ' ' : '')
            );
        }

        return 0;
    }

    public function shouldRun(): bool
    {
        if (Application::input()->getOption('no-hooks')) {
            return false;
        }

        if (Application::input()->getOption('no-hook')) {
            $disabledHooks = Application::input()->getOption('no-hook');
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
