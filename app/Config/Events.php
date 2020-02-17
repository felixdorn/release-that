<?php


namespace App\Config;


use Exception;
use Illuminate\Support\Arr;

class Events
{
    /**
     * @param array<int, string> $types
     * @return Events
     * @throws Exception
     */
    public static function emit(...$types)
    {
        $config = resolve('config');

        foreach ($types as $type) {
            if (!Arr::has($config['tasks'], $type)) {
                continue;
            }


            foreach ($config['tasks'][$type] as $task) {

                if (empty($task)) {
                    continue;
                }

                exec(sprintf('%s 2>&1', $task), $output, $ret);

                if ($ret !== 0) {
                    throw new Exception(sprintf("Task '%s' failed (exit-code %s).", $task, $ret), $ret);
                }

                die($ret);
            }
        }

        return new self;
    }

}
