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
use \ion\Dev\Templates\TransformTool;

class TemplatesCommand extends BaseCommand {

    protected function configure() {
        
        $this
            ->setName('templates')
            ->setDescription("Generate templates from a template definition.")
            ->setHelp("Recursively generates templates defined in XML fixtures. Templates and import and inherit other templates.")
            ->addArgument('operation', InputArgument::REQUIRED, "Either 'validate' or 'generate' - to validate or generate files respectively.")
            ->addArgument('fixtures', InputArgument::REQUIRED, "The XML template fixtures file.")            
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $args = new \stdClass;
        
        $args->operation = $input->getArgument('operation');
        $args->fixtures = $input->getArgument('fixtures');
        
        return TransformTool::create($args, $input, $output)->execute();
    }

}
