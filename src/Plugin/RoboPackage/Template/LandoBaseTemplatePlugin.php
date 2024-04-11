<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Template;

use RoboPackage\Core\Plugin\TemplatePluginBase;
use RoboPackage\Core\Attributes\TemplatePluginMetadata;

/**
 * Define the Lando base configuration template.
 */
#[TemplatePluginMetadata(
    id: 'lando.base',
    label: 'Lando Base',
    templateFile: 'lando.base.tpl.yml',
    templateDirs: [
        __DIR__ . '/../../../../templates'
    ]
)]
class LandoBaseTemplatePlugin extends TemplatePluginBase {}
