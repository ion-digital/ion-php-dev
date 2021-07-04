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
            ->addArgument('generator', InputArgument::OPTIONAL, "Either 'phpdoc' (https://phpdoc.org - the default) or 'phpdox' (https://phpdox.net).")            
            ->addOption('input', 'i', InputOption::VALUE_OPTIONAL, "The source input directory to scan (relative to the current working directory).", 'source/')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, "The output directory (relative to the current working directory).", 'documentation/')
            ->addOption('overwrite', 'ow', InputOption::VALUE_OPTIONAL, "Overwrite output files.", false)
        ;              
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $args = new \stdClass;
        
        $args->generator = strtolower($input->getArgument('generator'));                   
        $args->input = $input->getOption('input');
        $args->output = $input->getOption('output');
        $args->overwrite = (bool) $input->getOption('overwrite');

        return DocumentationTool::create($args, $input, $output)->execute();
    }

}
