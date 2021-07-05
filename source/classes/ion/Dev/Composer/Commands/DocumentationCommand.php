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
use \ion\Dev\Documentation\DocumentationTool;

class DocumentationCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('documentation')
            ->setDescription("Generate documentation using either PHP Documentor or PHP Dox.")
            ->setHelp("")
            ->addArgument('generator', InputArgument::OPTIONAL, "Currently only 'phpdoc' is supported (https://phpdoc.org).") // "Either 'phpdoc' (https://phpdoc.org - the default) or 'phpdox' (https://phpdox.net)."            
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, "The source input directory to scan (relative to the current working directory).", 'source/')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, "The output directory (relative to the current working directory).", 'documentation/html/')
            ->addOption('overwrite', 'ow', InputOption::VALUE_NONE, "Overwrite output files.")
            ->addOption('download', 'dl', InputOption::VALUE_NONE, "Force a new download of the generator PHAR binary.")
            ->addOption('ignore-certificate', 'ic', InputOption::VALUE_NONE, "Allow insecure connections to download the PHAR binary.")
        ;              
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        return (new DocumentationTool(
            
            $input->getArgument('generator') ?? 'phpdoc',
            explode(',', $input->getOption('input')),
            $input->getOption('output'),
            $input->getOption('overwrite'), 
            $input->getOption('download'),       
            $input->getOption('ignore-certificate'),
            $input,
            $output
                
        ))->execute();
    }

}
