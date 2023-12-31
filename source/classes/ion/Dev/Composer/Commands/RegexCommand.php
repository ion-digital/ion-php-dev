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
use \ion\Dev\Regex\RegexTool;

class RegexCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('regex')
            ->setDescription("Find and/or replace text in a file with a regular expression.")
            ->setHelp("This command allows you to load a file, search for text that matches the specified regular expression pattern and replace it with replacement text.")
            ->addArgument('operation', InputArgument::REQUIRED, "Either 'match' or 'replace.'")
            ->addArgument('pattern', InputArgument::REQUIRED, "The regular expression pattern to match (PCRE).")
            ->addArgument('input', InputArgument::OPTIONAL, "The path to the input file.", null)                
            ->addOption('replacement', 'r', InputOption::VALUE_OPTIONAL, "The replacement string - defaults to standard input if omitted.", '')            
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, "The path to the output file - defaults to standard output if omitted.", null)                
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, "The number of replacements to allow - defaults to none (no limit) if omitted.", -1)                
        ;                
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $args = new \stdClass;
        
        $args->operation = strtolower($input->getArgument('operation'));
        $args->pattern = $input->getArgument('pattern');
        //$args->replacement = ($input->getArgument('replacement') ? $input->getArgument('replacement') : '');
        $args->input = $input->getArgument('input');
        
        if($input->hasParameterOption('--replacement')) {
            
            $args->replacement = $input->getOption('replacement');
        } else {
            
            $args->replacement = false;
        }        
        
        
        $args->output = $input->getOption('output');
        $args->limit = intval($input->getOption('limit'));

        return RegexTool::create($args, $input, $output)->execute();
    }

}
