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
use \ion\Dev\Interfaces\InterfacesTool;

class InterfacesCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('interfaces')
            ->setDescription("Generate interfaces from source classes.")
            ->setHelp("")

            ->addArgument("action", InputArgument::OPTIONAL, "Either 'generate' to create interfaces or 'clean' to remove them from the input directory.", "generate")
                
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, "The source input directory to scan (relative to the current working directory).", 'source/classes/')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, "The output directory (relative to the current working directory).", 'source/interfaces/')
            ->addOption('overwrite', 'ow', InputOption::VALUE_NONE, "Overwrite output files.")
            ->addOption('filenames', null, InputOption::VALUE_OPTIONAL, "Masks, seperated by commas, to use when naming output files (where * is the input file basename). The first will contain the interface code, the rest will be derived from the first.", '*Interface.php')
                
        ;              
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
                
        return (new InterfacesTool(
            
            $input->getArgument('action'),
            $input->getOption('input'),
            $input->getOption('output'),
            $input->getOption('overwrite'),
            explode(",", $input->getOption('filenames')),    
            $input,
            $output
                
        ))->execute();        
    }

}
