<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Tests;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

class TestsTool extends Tool {

    public function execute(): int {

        $cwd = realpath(getcwd()) . DIRECTORY_SEPARATOR;
        
        return 0;
    }
}
