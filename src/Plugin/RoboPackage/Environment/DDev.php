<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Environment;

use RoboPackage\Core\RoboPackage;
use RoboPackage\Core\QuestionValidators;
use RoboPackage\Core\Utility\StringUtil;
use RoboPackage\Core\Plugin\EnvironmentPluginBase;
use RoboPackage\Core\Exception\RoboPackageRuntimeException;

/**
 * Define the DDev environment plugin.
 */
#[\EnvironmentPluginMetadata(
    id: 'ddev',
    label: 'DDev'
)]
class DDev extends EnvironmentPluginBase
{
    /**
     * @inheritDoc
     */
    public function setup(): static
    {
        $io = $this->io();

        try {
            /** @var \RoboPackage\Core\Contract\ExecutablePluginInterface $ddevExecutable */
            $ddevExecutable = $this->executableManager->createInstance('ddev');
            $ddevExecutable->setCommand('config');

            if ($projectName = $io->ask(
                question: 'Project Name',
                validator: QuestionValidators::requiredValue()
            )) {
                $projectMachineName = StringUtil::machineName($projectName);

                $ddevExecutable->setOption(
                    'project-name',
                    $projectMachineName
                );

                $ddevExecutable->setOption('web-environment-add', implode(',',
                    [
                        'APP_ENVIRONMENT=local',
                        "DRUSH_OPTIONS_URI=https://$projectMachineName.ddev.site"
                    ]
                ));
            }

            if ($projectDocroot = $io->ask(
                question: 'Project Docroot',
                default: 'web',
                validator: QuestionValidators::requiredValue()
            )) {
                $ddevExecutable->setOption('docroot', $projectDocroot);
            }

            if ($projectType = $io->choice(
                question: 'Project Type',
                choices: $this->projectTypeOptions()
            )) {
                $ddevExecutable->setOption('project-type', $projectType);
            }

            $phpVersions = RoboPackage::activePhpVersions();
            if ($projectPhpVersion = $io->choice(
                question: 'Project PHP Version',
                choices: $phpVersions,
                default: $phpVersions[1]
            )) {
                $ddevExecutable->setOption('php-version', $projectPhpVersion);
            }

            if ($projectServerType = $io->choice(
                question: 'Project Server Type',
                choices: [
                    'nginx-fpm',
                    'apache-fpm'
                ],
                default: 'nginx-fpm'
            )) {
                $ddevExecutable->setOption('webserver-type', $projectServerType);
            }
            $ddevExecutable->setOption('disable-settings-management', 'true');

            if ($command = $ddevExecutable->build()) {
                $result = $this->taskExec($command)->run();

                if (!$result->wasSuccessful()) {
                    throw new RoboPackageRuntimeException(
                        "There was a problem running the command: $command"
                    );
                }
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
        }

        return $this;
    }

    /**
     * The DDev project type options.
     *
     * @return array
     *   An array of project type options.
     */
    protected function projectTypeOptions(): array
    {
        $options = [];

        foreach ($this->projectTypes() as $key => $projectType) {
            if (!isset($projectType['versions'])) {
                $options[$key] = $projectType['label'];
            } else {
                foreach ($projectType['versions'] as $version) {
                    $options["$key$version"] = "{$projectType['label']}$version";
                }
            }
        }

        return $options;
    }

    /**
     * Define the DDev project types.
     *
     * @return array
     *   An array of the project types.
     */
    protected function projectTypes(): array
    {
        return [
            'drupal' => [
                'label' => 'Drupal',
                'versions' => [8, 9, 10]
            ],
            'magento' => [
                'label' => 'Magento',
                'versions' => [1, 2]
            ],
            'php' => ['label' => 'PHP'],
            'typo3' => ['label' => 'Typo3'],
            'laravel' => ['label' => 'Laravel'],
            'backdrop' => ['label' => 'Backdrop'],
            'shopware6' => ['label' => 'Shopware'],
            'wordpress' => ['label' => 'WordPress'],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function configureDatabases(): array
    {
        return [
            'primary' => [
                'type' => 'mysql',
                'database' => 'db',
                'username' => 'root',
                'password' => 'root',
                'host' => [
                    'type' => 'expression',
                    'configuration' => [
                        'data' => [
                            'type' => 'command',
                            'command' => 'ddev describe --json-output'
                        ],
                        'expression' => [
                            [
                                'query' => 'raw.dbinfo.host',
                                'connection' => 'internal'
                            ],
                            [
                                'query' => 'raw.hostname',
                                'connection' => 'external'
                            ]
                        ]
                    ]
                ],
                'port' => [
                    'type' => 'expression',
                    'configuration' => [
                        'data' => [
                            'type' => 'command',
                            'command' => 'ddev describe --json-output'
                        ],
                        'expression' => [
                            [
                                'query' => 'raw.dbinfo.dbPort',
                                'connection' => 'internal'
                            ],
                            [
                                'query' => 'raw.dbinfo.published_port',
                                'connection' => 'external'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    protected function configureEnvironment(): array
    {
        return [
            'ssh' => 'ddev ssh',
            'info' => 'ddev describe',
            'stop' => 'ddev stop',
            'start' => 'ddev start',
            'execute' => 'ddev exec',
            'restart' => 'ddev restart',
            'launch' => 'open --url $(ddev describe -j | jq --raw-output ".raw.primary_url")'
        ];
    }
}
