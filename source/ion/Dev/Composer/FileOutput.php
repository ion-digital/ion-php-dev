<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Composer;

/**
 * Description of ConsoleOutput
 *
 * @author Justus.Meyer
 */

//use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class FileOutput extends StreamOutput implements OutputInterface {
    
    private $handle;
    
    public function __construct(string $filename) {
        
        $path = getcwd() . DIRECTORY_SEPARATOR . $filename;

        $this->handle = fopen($path, 'w+b');
        
        parent::__construct($this->handle, StreamOutput::OUTPUT_NORMAL, false);
    }
    
    public function __destruct() {
        
        if($this->handle !== null) {
            
            fclose($this->handle);
        }
    }
    
    public function writeln($messages, $type = self::OUTPUT_NORMAL) {

        fwrite($this->handle, $messages);
    }
    
}
