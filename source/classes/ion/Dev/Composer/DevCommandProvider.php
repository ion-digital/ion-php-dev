<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Composer;

use Composer\Plugin\Capability\CommandProvider;
use ion\Dev\Composer\Commands\VcsCommand;
use ion\Dev\Composer\Commands\TemplatesCommand;
use ion\Dev\Composer\Commands\RegexCommand;

class DevCommandProvider implements CommandProvider {

    public function getCommands() {

        return [
            
            new VcsCommand(),
            new TemplatesCommand(),
            new RegexCommand()
            
        ];
    }

}
