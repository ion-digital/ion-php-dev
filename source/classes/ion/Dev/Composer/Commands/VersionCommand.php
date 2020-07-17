<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Composer\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\BaseCommand;
use \ion\Dev\Version\VersionTool;

class VersionCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('version')
            ->setDescription("Project SemVer version manipulation tools.")
            ->setHelp("Tools to help you manage your project's current package version, using build tools.")
                
            ->addArgument('version', InputArgument::REQUIRED, <<<HELP
The specified version to operate on:
    
    * 'auto' (checks version.json and then composer.json)
    * 'version' (version.json only)
    * 'composer' (composer.json only)
    * Any valid SemVer version (http://www.semver.org)        
HELP
            )
             
            ->addOption('print', 'p', InputOption::VALUE_OPTIONAL, <<<HELP
Print the version to the console, after applying options.
HELP
            )            
            ->addOption('check', 'c', InputOption::VALUE_OPTIONAL, <<<HELP
Check if the package version is higher than the specified version.
    
    * 'auto' (checks version.json and then composer.json)
    * 'version' (version.json only)
    * 'composer' (composer.json only)
    * Any valid SemVer version (http://www.semver.org)
        
If the package version is higher, composer will exit with error code 0 - otherwise with error code -1.
HELP
            )
                
            ->addOption('update', 'u', InputOption::VALUE_OPTIONAL, <<<HELP
Update the package version with the specified version.

    * 'version' (updates version.json only with the specified version) 
    * 'composer' (updates composer.json only with the specified version)
    * A command to execute (if --check is specified - typically to update a VCS tag)
    
If --check is specified, a check will determine whether the update is applied and the command will be executed.
HELP
            )
                
            ->addOption('increment', 'i', InputOption::VALUE_OPTIONAL, <<<HELP
Increment the version component with one unit, before updating or printing the version.
   
   * 'patch' (increment the patch component of the version with one)
   * 'minor' (increment the minor component of the version with one)
   * 'major' (increment the major component of the version with one)
HELP
            )
            ->addOption('release', 'r', InputOption::VALUE_OPTIONAL, <<<HELP
Override the release version component, before updating or printing the version.
HELP
            )
            ->addOption('build', 'b', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, <<<HELP
Add a build version component, before updating or printing the version.
HELP
            )
        ;  
        
        
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $args = new \stdClass;
        
//        $args->operation = strtolower($input->getArgument('operation'));
//        $args->pattern = $input->getArgument('pattern');
//        //$args->replacement = ($input->getArgument('replacement') ? $input->getArgument('replacement') : '');
//        $args->input = $input->getArgument('input');
//        
//        if($input->hasParameterOption('--replacement')) {
//            
//            $args->replacement = $input->getOption('replacement');
//        } else {
//            
//            $args->replacement = false;
//        }        
//        
//        
//        $args->output = $input->getOption('output');
//        $args->limit = intval($input->getOption('limit'));

        return VersionTool::create($args, $input, $output)->execute();
    }

}
