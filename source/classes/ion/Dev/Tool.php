<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev;

/**
 * Description of Tool
 *
 * @author Justus
 */

require_once(realpath(getcwd() . '/vendor/autoload.php'));

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use ion\Composer\Core\FileOutput;

abstract class Tool {
    
    public static function create(\stdClass $args = null, InputInterface $input = null, OutputInterface $output = null): Tool {
        
        return new static($args, $input, $output);
    }
    
    private $args;
    private $input;
    private $output;
    
    public function __construct(\stdClass $args = null, InputInterface $input = null, OutputInterface $output = null) {
        
        $this->args = ($args !== null ? $args : new \stdClass);
        $this->input = $input;
        //$this->output = ($output === null ? new FileOutput('output.log') : $output);
        $this->output = $output;
        
        if(defined("ION_COMPOSER")) {
            
            return;
        }
        
        define("ION_COMPOSER", true);
    }        
    
    protected function getArgs(): \stdClass {
        
        return $this->args;
    }    
    
    protected function getInputInterface(): ?InputInterface {
        
        return $this->input;
    }
    
    protected function getOutputInterface(): ?OutputInterface {
        
        return $this->output;
    }    
    
    protected function write(string $line = null): Tool {
        
        if($this->getOutputInterface() !== null) {
        
            $this->getOutputInterface()->write(($line === null ? '' : $line));
        }
        
        return $this;
    }    
    
    protected function writeln(string $line = null): Tool {
        
        if($this->getOutputInterface() !== null) {
        
            $this->getOutputInterface()->writeln(($line === null ? '' : $line));
        }
        
        return $this;
    }
}
