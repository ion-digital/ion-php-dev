<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Composer;

use Composer\Plugin\Capability\CommandProvider;
use ion\Dev\Composer\Commands\VcsCommand;
use ion\Dev\Composer\Commands\MacrosCommand;
use ion\Dev\Composer\Commands\RegexCommand;
use ion\Dev\Composer\Commands\VersionCommand;
use ion\Dev\Composer\Commands\BuildCommand;
use ion\Dev\Composer\Commands\ProjectCommand;
use ion\Dev\Composer\Commands\InterfacesCommand;
use ion\Dev\Composer\Commands\DocumentationCommand;

class DevCommandProvider implements CommandProvider {

    public function getCommands() {

        return [
            
            new VcsCommand(),
            new MacrosCommand(),
            new RegexCommand(),
            new VersionCommand(),
            new BuildCommand(),
            new ProjectCommand(),            
            //new TestsCommand(),
            new DocumentationCommand(),
            new InterfacesCommand()
            
        ];
    }

}

