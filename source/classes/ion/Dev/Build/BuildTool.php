<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Build;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

class BuildTool extends Tool {

    public function execute(): int {

        
        
        
        

        throw new Exception("Invalid operation: '{$this->getArgs()->operation}.'");
    }
}
