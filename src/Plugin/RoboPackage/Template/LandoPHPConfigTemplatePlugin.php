<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Template;

use RoboPackage\Core\Plugin\TemplatePluginBase;
use RoboPackage\Core\Attributes\TemplatePluginMetadata;

/**
 * Define the Lando PHP configuration template.
 */
#[TemplatePluginMetadata(
    id: 'lando.php.config',
    label: 'Lando PHP Configuration',
    templateFile: 'lando.php.config.tpl.ini',
    templateDirs: [
        __DIR__ . '/../../../../templates'
    ]
)]
class LandoPHPConfigTemplatePlugin extends TemplatePluginBase {}
