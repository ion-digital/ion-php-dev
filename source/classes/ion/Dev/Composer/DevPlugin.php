<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Composer;

/**
 * Description of CorePlugin
 *
 * @author Justus.Meyer
 */
use \Composer\Composer;
use \Composer\IO\IOInterface;
use \Composer\Plugin\PluginInterface;
use \Composer\Plugin\Capable;

class DevPlugin implements PluginInterface, Capable {

    public function activate(Composer $composer, IOInterface $io) {
        
        // Empty, for now!
    }
    
    public function deactivate(Composer $composer, IOInterface $io) {

        // Empty, for now!
    }

    public function uninstall(Composer $composer, IOInterface $io) {
        
        // Empty, for now!
    }    

    public function getCapabilities() {
        
        return array(
            'Composer\Plugin\Capability\CommandProvider' => 'ion\Dev\Composer\DevCommandProvider',
        );
    }

}
