<?php
declare(strict_types=1);

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
            'hooks' => Expect::anyOf(
                Expect::structure(
                    [
                    'beforeAll' => self::getHookSchema(),
                    'afterAll' => self::getHookSchema(),
                    'beforeRelease' => self::getHookSchema(),
                    'afterRelease' => self::getHookSchema(),
                    'beforeCommit' => self::getHookSchema(),
                    'afterCommit' => self::getHookSchema(),
                    'beforeTag' => self::getHookSchema(),
                    'afterTag' => self::getHookSchema(),
                    'beforePush' => self::getHookSchema(),
                    'afterPush' => self::getHookSchema(),
                    ]
                ),
                false
            )
            ]
        );
    }

    private static function getHookSchema(): AnyOf
    {
        return Expect::anyOf(
            Expect::arrayOf(
                Expect::string('')
            ),
            Expect::string('')
        );
    }
}
