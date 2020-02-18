<?php

namespace App\Events;

use App\App;
use Exception;
use Illuminate\Support\Arr;

class Events
{

    /**
     * @param  array<int, string> $types
     * @throws Exception
     */
    public function emit(...$types): void
    {
        foreach ($types as $type) {
            if (!Arr::has(App::config('tasks'), $type)
                || empty(App::config('tasks.' . $type))
            ) {
                continue;
            }

            /**
             * @var Hook[] $hooks
             */
            $hooks = App::config('tasks.' . $type);

            foreach ($hooks as $hook) {
                if ($hook->shouldRun()) {
                    $hook->run();
                }
            }
        }
    }
}
