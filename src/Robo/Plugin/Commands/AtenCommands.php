<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Robo\Plugin\Commands;

use Robo\Tasks;
use Robo\Symfony\ConsoleIO;
use RoboPackage\Core\RoboPackage;
use Robo\Contract\ConfigAwareInterface;
use RoboPackage\Core\Traits\EnvironmentCommandTrait;
use RoboPackage\Core\Plugin\Manager\EnvironmentManager;
use RoboPackage\Core\Plugin\Manager\InstallableManager;
use RoboPackage\Core\Exception\RoboPackageRuntimeException;

/**
 * Define the Aten Robo package commands
 */
class AtenCommands extends Tasks implements ConfigAwareInterface
{
    use EnvironmentCommandTrait;

    /**
     * @var \RoboPackage\Core\Plugin\Manager\EnvironmentManager
     */
    protected EnvironmentManager $environmentManager;

    /**
     * @var \RoboPackage\Core\Plugin\Manager\InstallableManager
     */
    protected InstallableManager $installableManager;

    /**
     * The command class constructor.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->environmentManager = RoboPackage::getContainer()->get('environmentManager');
        $this->installableManager = RoboPackage::getContainer()->get('installableManager');
    }

    /**
     * The project default framework.
     */
    protected const FRAMEWORK_DEFAULT = 'drupal';

    /**
     * The project default local environment.
     */
    protected const ENVIRONMENT_DEFAULT = 'ddev';

    /**
     * Run the initialize or update command for an Aten project.
     *
     * @param string $action
     *   The action you want to invoke for the project is available options
     *   are (init, update).
     */
    public function atenProject(ConsoleIO $io, string $action = 'init'): void
    {
        try {
            switch ($action) {
                case 'init':
                    $io->title(
                        'This wizard will guide you through setting up an ' .
                        'Aten-based project.'
                    );
                    $this
                        ->setupEnvironment($io)
                        ->setupFramework($io)
                        ->launchApplication($io);
                    break;
                case 'update':
                    // Placeholder to run project updates
                    break;
                default:
                    throw new RoboPackageRuntimeException(sprintf(
                        'The action provided for the Aten project (%s) is invalid.',
                        $action
                    ));
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }
    }

    /**
     * Set up a supported Aten environment.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *   The console I/O instance.
     */
    protected function setupEnvironment(ConsoleIO $io): static
    {
        $environmentManager = $this->environmentManager;

        if (
            ($environments = $environmentManager->getDefinitionOptions())
            && !empty($environments)
        ) {
            try {
                $io->note(
                    'The first step is setting up the environment that will ' .
                    'run the project.'
                );
                $environment = $io->choice(
                    'Which environment do you want to use?',
                    $environments,
                    self::ENVIRONMENT_DEFAULT
                );
                /** @var \RoboPackage\Core\Contract\EnvironmentPluginInterface $instance */
                $instance = $environmentManager->createInstance($environment);
                $instance
                    ->setup()
                    ->configure();
            } catch (\Exception $exception) {
                $io->error($exception->getMessage());
            }
        }

        if ($this->confirm('Start the environment?', true)) {
            $this->runEnvironmentCommand('start');
        } else {
            $io->warning(
                'The installation process has been aborted because no ' .
                'environment was started.'
            );
            exit;
        }

        return $this;
    }

    /**
     * Set up a supported Aten framework.
     *
     * @param \Robo\Symfony\ConsoleIO $io
     *   The console IO service.
     */
    protected function setupFramework(ConsoleIO $io): static
    {
        $installableManager = $this->installableManager;

        if (
            ($frameworks = $installableManager->getDefinitionOptionsByGroup('framework'))
            && !empty($frameworks)
        ) {
            try {
                $io->note(
                    'The next step is setting up the application that will ' .
                    'used for the project.'
                );
                $framework = $io->choice(
                    'Which framework do you want to use?',
                    $frameworks,
                    self::FRAMEWORK_DEFAULT
                );
                /** @var \RoboPackage\Core\Contract\InstallablePluginInterface $instance */
                $instance = $installableManager->createInstance($framework);
                $instance->runInstallation();
            } catch (\Exception $exception) {
                $io->error($exception->getMessage());
            }
        }

        return $this;
    }

    /**
     * Launch the application in the browser.
     *
     * @param ConsoleIO $io
     *   The console IO.
     * @return static
     *   The current instance of the class.
     */
    protected function launchApplication(ConsoleIO $io): static
    {
        if ($io->confirm('Would you like to launch the application?')) {
            $this->runEnvironmentCommand('launch');
        }

        return $this;
    }
}
