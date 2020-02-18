<?php

namespace App;

use Exception;
use Illuminate\Support\Arr;

class Events
{

    /**
     * @param array<int, string> $types
     * @throws Exception
     */
    public function emit(...$types)
    {
        $config = Application::config();

        foreach ($types as $type) {
            if (!Arr::has($config['tasks'], $type) || empty($config['tasks'][$type])) {
                continue;
            }

            /** @var Hook[] $hooks */
            $hooks = $config['tasks'][$type];

            foreach ($hooks as $hook) {
                if ($hook->shouldRun()) {
                    $hook->run();
                }
            }
        }
    }
}
