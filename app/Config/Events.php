<?php


namespace App\Config;


use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\Input;

class Events
{
    /**
     * @param array<int, string> $types
     * @throws Exception
     */
    public static function emit(...$types)
    {
        $config = resolve('config');
        /** @var Input $io */
        $i = resolve('input');
        /** @var OutputStyle $o */
        $o = resolve('output');
        if ($i->getOption('no-hooks')) {
            $o->comment('Skipping ' . implode(', ', $types));
            return;
        }

        $disabledHooks = [];
        if ($i->getOption('no-hook')) {
            $disabledHooks = explode(',', $i->getOption('no-hook'));
        }


        foreach ($types as $type) {
            $isDisabled = false;

            foreach ($disabledHooks as $disabledHook) {
                if ($disabledHook === $type) {
                    $o->comment("Skipped {$type}");
                    $isDisabled = true;
                }
            }

            if ($isDisabled) {
                continue;
            }

            if (!Arr::has($config['tasks'], $type)) {
                continue;
            }


            foreach ($config['tasks'][$type] as $task) {

                if (empty($task)) {
                    continue;
                }

                echo exec(sprintf('%s 2>&1', $task), $output, $ret);

                if ($ret !== 0) {
                    throw new Exception(sprintf("Task '%s' failed (exit-code %s).", $task, $ret), $ret);
                }

            }
        }
    }

}
