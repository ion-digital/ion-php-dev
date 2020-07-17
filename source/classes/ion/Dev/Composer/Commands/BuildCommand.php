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
use \ion\Dev\Regex\BuildTool;

//Usage: D:\prj\dev\ion\php\autoloader\vendor\bin\/../ion/php-trans-porter/bin/php-trans-porter [options]
//
//PHP Trans-porter converts code-base or individual files from one PHP version to
//another. This is useful for automaticall back-porting your code to a previous
//PHP version. It currently supports the following conversions:
//
//7.2 -> 5.6
//
//Options:
//
//--help                  Display this help screen.
//--target-version        The target PHP version to convert to (required if --help was
//                        not specified).
//--source-version        The source PHP version to convert from (defaults to PHP 7.2)
//--input                 The input directory OR input file (defaults to current directory).
//--output                The output directory OR output file (defaults to target PHP version).
//--non-recursive         If specified and the input parameter is a directory, then
//                        only files in the specified directory will be converted.

class BuildCommand extends BaseCommand {

    protected function configure() {

        $this
            ->setName('build')
            ->setDescription("")
            ->setHelp("")
            ->addArgument('', InputArgument::REQUIRED, "")
            ->addOption('replacement', 'r', InputOption::VALUE_OPTIONAL, "The replacement string - defaults to standard input if omitted.", '')
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

        return BuildTool::create($args, $input, $output)->execute();
    }

}
