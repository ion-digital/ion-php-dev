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
use \ion\Dev\Vcs\VcsTool;

class VcsCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('vcs')
            ->setDescription("Add, commit, push or pull the current code to the VCS repository and/or update the current version.")
            ->setHelp("Add, commit or push the current code to the VCS repository and/or update the current version using Mercurial/HG or GIT.")                
            
            ->addArgument('operation', InputArgument::REQUIRED, "Either 'add', 'commit', 'push', 'pull' or 'version.'")        

            ->addOption('check', 'c', InputOption::VALUE_OPTIONAL, "Check the current VCS version. Either 'auto' (checks version.json and then composer.json, which is the default), 'version' (version.json only), 'composer' (composer.json only) or a valid SemVer version (http://www.semver.org).")
            ->addOption('update', 'u', InputOption::VALUE_NONE, "Update the current VCS version.")
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, "Suppress other output and display the current VCS version.")

            ->addOption('paths', 'p', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, "A list of paths to use in either the 'add' or 'commit' actions; either directories (contents will be processed recursively) or files.")              
            
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, "The commit message.")    
                
            ->addOption('prepend', null, InputOption::VALUE_OPTIONAL, "Text to prepend to the output when displaying the VCS version.")
            ->addOption('append', null, InputOption::VALUE_OPTIONAL, "Text to append to the output when displaying the VCS version.")
        ;                
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $args = new \stdClass;
        

        $args->operation = $input->getArgument('operation');
        $args->check = $input->getOption('check');

        if($input->hasParameterOption('--output')) {
            
            $args->output = $input->getOption('output');
            
            if($input->hasParameterOption('--prepend')) {

                $args->prepend = $input->getOption('prepend');
            }       
            
            if($input->hasParameterOption('--append')) {

                $args->append = $input->getOption('append');
            }    
            
        } else {
            
            $args->output = false;
        }
        

        $args->update = $input->getOption('update');
        $args->paths = $input->getOption('paths');
        $args->message = $input->getOption('message');                            

        return VcsTool::create($args, $input, $output)->execute();
    }

}

