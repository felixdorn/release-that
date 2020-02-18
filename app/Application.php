<?php


namespace App;

use GitElephant\Repository;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;

class Application
{
    /**
     * @var null|Application
     */
    private static ?Application $uniqueInstance = null;

    /**
     * @var InputInterface
     */
    private static InputInterface $input;

    /**
     * @var OutputStyle
     */
    private static OutputStyle $output;

    /**
     * @var array
     */
    private static array $configuration;
    /**
     * @var Repository
     */
    private static Repository $repository;


    /**
     * @param InputInterface $input
     * @param OutputStyle $output
     * @param array $configuration
     * @param Repository $repository
     */
    protected function __construct(
        InputInterface $input,
        OutputStyle $output,
        array $configuration,
        Repository $repository
    )
    {
        self::$input = $input;
        self::$output = $output;
        self::$configuration = $configuration;
        self::$repository = $repository;
    }

    /**
     * @return string
     */
    public static function cwd(): string
    {
        return getcwd() . '/';
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
     * @return Application
     */
    public static function get(): Application
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
     * @param string|null $path
     * @return array|string|null
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
}
