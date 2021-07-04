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
use \ion\Dev\Generate\GenerateTool;

class TestsCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('tests')
            ->setDescription("")
            ->setHelp("");
            //->addArgument('', InputArgument::REQUIRED, "")
            //->addOption('replacement', 'r', InputOption::VALUE_OPTIONAL, "The replacement string - defaults to standard input if omitted.", '')
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

        return TestsTool::create($args, $input, $output)->execute();
    }

}
