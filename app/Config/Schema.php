<?php

namespace App\Config;

use Nette\Schema\Elements\AnyOf;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class Schema
{

    public static function getSchema(): Structure
    {
        return Expect::structure(
            [
            'commit' => Expect::anyOf(
                Expect::structure(
                    [
                    'message' => Expect::string('chore: release {version}'),
                    'empty' => Expect::bool(false),
                    'stageAll' => Expect::bool(true)
                    ]
                ),
                false
            ),
            'push' => Expect::anyOf(
                Expect::structure(
                    [
                    'remote' => Expect::string('origin'),
                    'arguments' => Expect::string('')
                    ]
                ),
                false
            ),
            'tag' => Expect::anyOf(
                Expect::structure(
                    [
                    'name' => Expect::string('{version}'),
                    'message' => Expect::string('Release Tag {version}'),
                    ]
                ),
                false
            ),
            'tasks' => Expect::anyOf(
                Expect::structure(
                    [
                    'beforeAll' => self::getTasksSchema(),
                    'afterAll' => self::getTasksSchema(),
                    'beforeRelease' => self::getTasksSchema(),
                    'afterRelease' => self::getTasksSchema(),
                    'beforeCommit' => self::getTasksSchema(),
                    'afterCommit' => self::getTasksSchema(),
                    'beforeTag' => self::getTasksSchema(),
                    'afterTag' => self::getTasksSchema(),
                    'beforePush' => self::getTasksSchema(),
                    'afterPush' => self::getTasksSchema(),
                    ]
                ),
                false
            )
            ]
        );
    }

    private static function getTasksSchema(): AnyOf
    {
        return Expect::anyOf(
            Expect::arrayOf(
                Expect::string('')
            ),
            Expect::string('')
        );
    }
}
