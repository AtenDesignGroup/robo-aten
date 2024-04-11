<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Environment;

use RoboPackage\Core\Plugin\EnvironmentPluginBase;
use RoboPackage\Core\Contract\TemplatePluginInterface;

/**
 * Define the Lando environment plugin.
 */
#[\EnvironmentPluginMetadata(
    id: 'lando',
    label: 'Lando'
)]
class Lando extends EnvironmentPluginBase
{
    /**
     * @inheritDoc
     */
    public function setup(): static
    {
        $templateManager = $this->templateManager;
        $rootPath = $templateManager->getRootPath();

        $templateManager->process(
            'lando.php.config',
            function(TemplatePluginInterface $template) use ($rootPath) {
                if (!file_exists("$rootPath/.lando")) {
                    $this->_mkdir("$rootPath/.lando");
                }
                $toPath = "$rootPath/.lando/php.ini";
                $this->copyToPath(
                    $toPath,
                    $template,
                );
            }
        );

        $templateManager->process(
            'lando.project',
            function(TemplatePluginInterface $template) use ($rootPath) {
                $toPath = "$rootPath/.lando.yml";
                $this->copyToPath(
                    $toPath,
                    $template,
                );
            }
        );
        $templateManager->process(
            'lando.base',
            function(TemplatePluginInterface $template) use ($rootPath) {
                $toPath = "$rootPath/.lando.base.yml";
                $this->copyToPath(
                    $toPath,
                    $template
                );
            }
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function configureDatabases(): array
    {
        return [
            'primary' => [
                'type' => 'mysql',
                'database' => 'drupal',
                'username' => 'drupal',
                'password' => 'drupal',
                'host' => [
                    'type' => 'expression',
                    'configuration' => [
                        'data' => [
                            'type' => 'command',
                            'command' => 'lando info --service database --format json'
                        ],
                        'expression' => '[].{{ connection }}_connection.host | [0]'
                    ]
                ],
                'port' => [
                    'type' => 'expression',
                    'configuration' => [
                        'data' => [
                            'type' => 'command',
                            'command' => 'lando info --service database --format json'
                        ],
                        'expression' => '[].{{ connection }}_connection.port | [0]'
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function configureEnvironment(): array
    {
        return [
            'ssh' => 'lando ssh',
            'stop' => 'lando poweroff && docker rm $(docker ps -aqf "status=exited")',
            'start' => 'lando start',
            'execute' => 'lando ssh --command',
            'restart' => 'lando restart',
            'launch' => 'open --url $(lando info --service appserver_nginx --format json | jq --raw-output ".[].urls[-1]")'
        ];
    }
}
