<?php
declare(strict_types=1);

namespace App\Events;

use App\App;
use Exception;
use Illuminate\Support\Arr;

class Events
{

    /**
     * @param array<int, string> $types
     * @throws Exception
     */
    public function emit(...$types): void
    {
        foreach ($types as $type) {
            if (!Arr::has(App::config('hooks'), $type)
                || empty(App::config('hooks.' . $type))
            ) {
                continue;
            }

            /**
             * @var Hook[] $hooks
             */
            $hooks = App::config('hooks.' . $type);

            foreach ($hooks as $hook) {
                if ($hook->shouldRun()) {
                    $hook->run();
                }
            }
        }
    }
}
