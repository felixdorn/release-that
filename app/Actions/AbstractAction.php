<?php
declare(strict_types=1);

namespace App\Actions;

use App\Version\VersionManager;
use Exception;
use Illuminate\Console\OutputStyle;
use PHLAK\SemVer\Version;
use Symfony\Component\Console\Input\InputInterface;

class AbstractAction implements ActionInterface
{

    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputStyle
     */
    protected $output;
    /**
     * @var string[]|bool[]
     */
    protected $configuration;

    /**
     * AbstractAction constructor.
     *
     * @param InputInterface  $input
     * @param OutputStyle     $output
     * @param string[]|bool[] $configuration
     */
    public function __construct(
        InputInterface $input,
        OutputStyle $output,
        array $configuration
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function do(VersionManager $manager, Version $version)
    {
        throw new Exception('An action must do something in the `do` method');
    }

    /***
     * @return bool
     */
    protected function isDryRun(): bool
    {
        return (bool)$this->input->getOption('dry-run');
    }
}
