<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Backporting;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;

// cls && echo "VERSION" | composer regex match "/x/"

class BackportTool extends Tool {

    public function execute(): int {

        //print_r($this->getArgs());
        
        $replacement = '';    
        $pattern = $this->getArgs()->pattern;
        $limit = intval($this->getArgs()->limit);
        $output = $this->getArgs()->output;
        
        if($this->getArgs()->replacement !== false) {

            $replacement = $this->getArgs()->replacement;

        } else {

            if (0 === ftell(STDIN)) {

                while (!feof(STDIN)) {
                    
                    $replacement .= fread(STDIN, 1024);
                }
            }
        }
        
        if(empty($this->getArgs()->input) || !file_exists(realpath($this->getArgs()->input))) {

            throw new Exception("Input file '{$this->getArgs()->input}' does not exist.");
        }
        
        //var_dump($this->getArgs()->input);

        $f = file_get_contents(realpath($this->getArgs()->input));        


        if($this->getArgs()->operation === 'match') {
            
            if(preg_match($pattern, $f) === 1) {

                $this->writeln("Match found.");
                return 0;
            }
            
            $this->writeln("Match NOT found.");
            return -1;
        }
        
        if($this->getArgs()->operation === 'replace') {
        
            $f = preg_replace($pattern, $replacement, $f, $limit);

            if($output === null) {
            
                echo $f;
                return 0;
            }
                        
            file_put_contents($output, $f);
            $this->writeln("'{$output}' written.");
            
            return 0;
        }        

        throw new Exception("Invalid operation: '{$this->getArgs()->operation}.'");
    }

    //protected function 
}
