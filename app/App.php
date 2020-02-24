<?php
declare(strict_types=1);

namespace App;

use App\Events\Events;
use GitElephant\Repository;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;

class App
{
    /**
     * @var null|App
     */
    private static $uniqueInstance = null;

    /**
     * @var InputInterface
     */
    private static $input;

    /**
     * @var OutputStyle
     */
    private static $output;

    /**
     * @var string[]|bool[]
     */
    private static $configuration;
    /**
     * @var Repository
     */
    private static $repository;
    /**
     * @var string
     */
    private static $version = null;


    /**
     * @param InputInterface  $input
     * @param OutputStyle     $output
     * @param string[]|bool[] $configuration
     * @param Repository      $repository
     */
    protected function __construct(
        InputInterface $input,
        OutputStyle $output,
        array $configuration,
        Repository $repository
    ) {
        self::$input = $input;
        self::$output = $output;
        self::$configuration = $configuration;
        self::$repository = $repository;
    }

    /**
     * @param  string $path
     * @return string
     */
    public static function cwd(string $path = ''): string
    {
        return getcwd() . '/' . $path;
    }

    /**
     * @return InputInterface
     */
    public static function input(): InputInterface
    {
        self::get();
        return self::$input;
    }

    /**
     * @return App
     */
    public static function get(): App
    {
        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new self(
                resolve('input'),
                resolve('output'),
                resolve('config'),
                resolve('git')
            );
        }

        return self::$uniqueInstance;
    }

    /**
     * @return OutputStyle
     */
    public static function output(): OutputStyle
    {
        self::get();
        return self::$output;
    }

    /**
     * @param  string|null $path
     * @return mixed
     */
    public static function config(?string $path = null)
    {
        self::get();

        if ($path === null) {
            return self::$configuration;
        }

        $paths = explode('.', $path);
        $result = self::$configuration;

        foreach ($paths as $pathPart) {
            if (!is_array($result)) {
                return $result;
            }
            if (array_key_exists($pathPart, $result)) {
                $result = $result[$pathPart];
            }
        }

        return $result;
    }

    public static function git(): Repository
    {
        self::get();
        return self::$repository;
    }

    public static function events(): Events
    {
        return new Events();
    }

    public static function version(string $version = null)
    {
        self::get();

        if ($version !== null) {
            self::$version = $version;
        }

        return self::$version;
    }
}
