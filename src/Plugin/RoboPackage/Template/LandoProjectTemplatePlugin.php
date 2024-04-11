<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Template;

use Robo\Symfony\ConsoleIO;
use RoboPackage\Core\RoboPackage;
use RoboPackage\Core\Utility\StringUtil;
use RoboPackage\Core\QuestionValidators;
use RoboPackage\Core\Attributes\TemplatePluginMetadata;
use RoboPackage\Core\Plugin\TemplatePluginBase;

/**
 * Define the Lando project configuration template.
 */
#[TemplatePluginMetadata(
    id: 'lando.project',
    label: 'Lando Project',
    templateFile: 'lando.project.tpl.yml',
    templateDirs: [
        __DIR__ . '/../../../../templates'
    ]
)]
class LandoProjectTemplatePlugin extends TemplatePluginBase
{
    /**
     * Define the Lando webserver default.
     */
    protected const LANDO_WEBSERVER_DEFAULT = 'nginx';

    /**
     * @inheritDoc
     */
    protected function variableDefinitions(): array
    {
        return [
            'projectName' => [
                'variable' => '{{projectName}}',
                'callback' => function(ConsoleIO $io) {
                    $value = $io->ask(
                        question: 'Input Project Name',
                        validator: QuestionValidators::requiredValue()
                    );
                    if (!isset($value)) {
                        return NULL;
                    }

                    return StringUtil::machineName($value);
                },
            ],
            'projectRecipe' => [
                'variable' => '{{projectRecipe}}',
                'callback' => function (ConsoleIO $io) {
                    $recipeOptions = $this->getLandoRecipeOptions();
                    $projectRecipe = $io->choice(
                        'Select Project Recipe',
                        $recipeOptions['primary']
                    );

                    if (isset($recipeOptions['secondary'][$projectRecipe])) {
                        $recipeVersions = $recipeOptions['secondary'][$projectRecipe];
                        $version = $io->choice(
                            'Select Recipe Version',
                            $recipeVersions,
                            array_key_last($recipeVersions)
                        );
                        $projectRecipe .= $version;
                    }

                    return $projectRecipe;
                },
            ],
            'projectServer' => [
                'variable' => '{{projectServer}}',
                'callback' => function (ConsoleIO $io) {
                    return $io->choice(
                        'Select Project Server',
                        $this->getLandoServerOptions(),
                        static::LANDO_WEBSERVER_DEFAULT
                    );
                },
            ],
            'phpVersion' => [
                'variable' => '{{phpVersion}}',
                'callback' => function(ConsoleIO $io) {
                    $phpVersions = RoboPackage::activePhpVersions();
                    return $io->choice(
                        'Input PHP Version',
                        $phpVersions,
                        $phpVersions[1]
                    );
                },
            ]
        ];
    }

    /**
     * Define the Lando recipe definitions.
     *
     * @return array
     *   An array of the Lando recipes.
     */
    protected function landoRecipeDefinitions(): array
    {
        return [
            'laravel' => [
                'label'=> 'Laravel'
            ],
            'symfony' => [
                'label' => 'Symfony'
            ],
            'backdrop' => [
                'label'=> 'Backdrop'
            ],
            'wordpress' => [
                'label'=> 'WordPress'
            ],
            'drupal' => [
                'label' => 'Drupal',
                'versions' => range(8, 10)
            ],
        ];
    }

    /**
     * Get the Lando web server options.
     *
     * @return string[]
     *   An array of the Lando server options.
     */
    protected function getLandoServerOptions(): array
    {
        return [
            'nginx' => 'Nginx',
            'apache' => 'Apache',
        ];
    }

    /**
     * Get the Lando recipe options.
     *
     * @return array
     *   An array of Lando recipe options.
     */
    protected function getLandoRecipeOptions(): array
    {
        $options = [];

        foreach ($this->landoRecipeDefinitions() as $key => $definition) {
            if (!isset($definition['label'])) {
                continue;
            }
            $options['primary'][$key] = $definition['label'];

            if (isset($definition['versions'])) {
                $options['secondary'][$key] = $definition['versions'];
            }
        }
        ksort($options['primary']);

        return $options;
    }
}
