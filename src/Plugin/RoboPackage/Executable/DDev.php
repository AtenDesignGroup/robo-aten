<?php

declare(strict_types=1);

namespace RoboPackage\Aten\Plugin\RoboPackage\Executable;

use RoboPackage\Core\Plugin\ExecutablePluginBase;
use RoboPackage\Core\Attributes\ExecutablePluginMetadata;

/**
 * Define the DDev executable.
 */
#[ExecutablePluginMetadata(
    id: 'ddev',
    label: 'DDev',
    binary: 'ddev'
)]
class DDev extends ExecutablePluginBase {}
