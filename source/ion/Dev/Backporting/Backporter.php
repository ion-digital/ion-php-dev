<?php

namespace ion\Dev\Backporting;

/**
 * Description of Backporting
 *
 * @author Justus
 */
class Backporting {

    public static function main(array $args = []) {
        new static($args);
    }

    protected function __construct(array $args) {
        $parameters = $this->parseArgs($args);

        $state = $this->createState($parameters, $args); 
        
        $validationResult = $this->validateState($state);

        $this->showHeader();
        
        if ($validationResult === null) {
            exit($this->executeState($state));
        }

        echo "\nERROR: " . trim($validationResult) . "\n";

        exit(-1);
    }

    protected function parseArgs(array $args): array {

        $result = [
            'help' => false,
            'target-version' => null,
            'source-version' => [ BackportingConstants::__BASE_PHP_MAJOR_VERSION , BackportingConstants::__BASE_PHP_MINOR_VERSION ],
            'input' => getcwd() . DIRECTORY_SEPARATOR,
            'output' => getcwd() . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR,
            'non-recursive' => false
        ];



        $parameter = null;

        foreach ($args as $arg) {
            
            $arg = trim($arg);
            
            if($parameter !== null) {
                
                if ($parameter === 'target-version') {

                    $result[$parameter] = explode('.', $arg);
                    
                } else if ($parameter === 'source-version') {

                    $result[$parameter] = explode('.', $arg);
                    
                } else if ($parameter === 'input') {

                    $result[$parameter] = $arg;
                    
                } else if ($parameter === 'output') {

                    $result[$parameter] = $arg;
                    
                } 
                
                $parameter = null;
            }
            
            if (strpos($arg, '--', 0) === 0) {
               
                if ($arg === '--target-version') {

                    $parameter = 'target-version';
                    
                } else if ($arg === '--source-version') {

                    $parameter = 'source-version';
                    
                } else if ($arg === '--input') {

                    $parameter = 'input';
                    
                } else if ($arg === '--output') {

                    $parameter = 'output';
                    
                } else if ($arg === '--non-recursive') {
                    
                    $result['non-recursive'] = true;
                    $parameter = null;
                } else if ($arg === '--help') {
                    
                    $result['help'] = true;
                    $parameter = null;
                }
            }                                               
            
        }


        return $result;
    }

    protected function createState(array $parameters, array $args): array {
        
        $state = [];
        
        $state['command'] = $args[0];                
        $state['help'] = $parameters['help'];
        
        $state['input-dir'] = $parameters['input'];
        $state['output-dir'] = $parameters['output'];

        $state['input-dir'] = preg_replace('/\+/', DIRECTORY_SEPARATOR, $state['input-dir']);        
        $state['input-dir'] = preg_replace('/\/+/', DIRECTORY_SEPARATOR, $state['input-dir']);
        
        $state['output-dir'] = preg_replace('/\+/', DIRECTORY_SEPARATOR, $state['output-dir']);
        $state['output-dir'] = preg_replace('/\/+/', DIRECTORY_SEPARATOR, $state['output-dir']);
        
        if(strrpos($state['input-dir'], DIRECTORY_SEPARATOR, strlen($state['input-dir']) - 1) === false) {
            if(is_dir($state['input-dir']) === false) {
                $state['input-dir'] = dirname($state['input-dir']);
            }
        }                

        if(strrpos($state['output-dir'], DIRECTORY_SEPARATOR, strlen($state['output-dir']) - 1) === false) {
            if(is_dir($state['output-dir']) === false) {
                $state['output-dir'] = dirname($state['output-dir']);
            }
        }            
    

        $inputFile = null;
        $outputFile = null;
        
        if(strrpos(trim($parameters['input']), '.php') !== false) {                       
            
            $inputFile = basename($parameters['input']);
            
            if(strrpos($parameters['output'], '.php') === false) {
                $outputFile = basename($parameters['input']);
            } else {
                $outputFile = basename($parameters['output']);
            }
            
            if(strrpos(trim($parameters['output']), '.php') !== false) { 
                
            }
        }
            
        if(strrpos($state['input-dir'], DIRECTORY_SEPARATOR, strlen($state['input-dir']) - 1) === false) {
            $state['input-dir'] = $state['input-dir'] . DIRECTORY_SEPARATOR;            
        }             

        if(strrpos($state['output-dir'], DIRECTORY_SEPARATOR, strlen($state['output-dir']) - 1) === false) {
            $state['output-dir'] = $state['output-dir'] . DIRECTORY_SEPARATOR;            
        }              
        
        
        $state['input-file'] = $inputFile;
        $state['output-file'] = $outputFile;
        
        $state['source-version'] = $parameters['source-version'];
        $state['target-version'] = $parameters['target-version'];
        
        $state['non-recursive'] = $parameters['non-recursive'];
        
        return $state;
    }
    
    protected function validateState(array $state): ?string {

        if ($state['help'] === true) {
            return null;
        }
     
        if ($state['target-version'] === null) {
            return 'The target version needs to be specified (e.g. --target-version 5.6).';
        }
        
        //var_dump($parameters['input-dir']);
        //exit;
        
        if(is_dir($state['input-dir']) === false) {
            return 'The input directory does not exist.';
        }
        
        if($state['input-file'] !== null) {
            if(file_exists($state['input-dir'] . DIRECTORY_SEPARATOR . $state['input-file']) === false) {
                return 'The input file does not exist.';
            }
        }
        
        return null; // No validation errors occurred
    }    
    
    protected function executeState(array $state): int {       

        if ($state['help'] === true) {
            $this->showHelp($state['command']);
            return 0;
        }

        //print_r($state);
        
        $conversion = null;
        $converter = new Converter($state['source-version'], $state['target-version']);
        
        if($state['input-file'] !== null) {
            
            // We're only converting one file
            
            $conversion = new PhpFile($state['input-dir'] . $state['input-file'], $converter);

        } else {
            
            // We're converting a directory
            
            $conversion = new PhpDirectory($state['input-dir'], $converter, ($state['non-recursive'] === false));

        }
        
        $conversion->convert($state['output-dir'] . ($state['output-file'] !== null ? $state['output-file'] : ''));

        return 0;
    }

    protected function showHeader() {
        $appName = BackportingConstants::__APP_NAME;

        $majorVersion = BackportingConstants::__MAJOR_VERSION;
        $minorVersion = BackportingConstants::__MINOR_VERSION;
        $patchVersion = BackportingConstants::__PATCH_VERSION;

        $header = <<<HEADER

$appName $majorVersion.$minorVersion.$patchVersion
    
HEADER;
        
        echo trim($header) . "\n\n";
    }

    protected function showHelp(string $cmdName) {

        $baseMajorVersion = BackportingConstants::__BASE_PHP_MAJOR_VERSION;
        $baseMinorVersion = BackportingConstants::__BASE_PHP_MINOR_VERSION;

        $minPhpMajorVersion = BackportingConstants::__MIN_PHP_MAJOR_VERSION;
        $minPhpMinorVersion = BackportingConstants::__MIN_PHP_MINOR_VERSION;

        $conversions = [
            $baseMajorVersion . '.' . $baseMinorVersion => $minPhpMajorVersion . '.' . $minPhpMinorVersion
        ];

        $tmp = [];
        foreach ($conversions as $key => $value) {
            $tmp[] = "$key -> $value";
        }
        $conversionsStr = trim(join('; ', $tmp));

        echo <<<HELP
Usage: $cmdName [options]

PHP Trans-porter converts code-base or individual files from one PHP version to
another. This is useful for automaticall back-porting your code to a previous
PHP version. It currently supports the following conversions:
                
$conversionsStr
       
Options: 
    
--help           \tDisplay this help screen.
--target-version \tThe target PHP version to convert to (required if --help was
                 \tnot specified).
--source-version \tThe source PHP version to convert from (defaults to PHP $baseMajorVersion.$baseMinorVersion)
--input          \tThe input directory OR input file (defaults to current directory).
--output         \tThe output directory OR output file (defaults to target PHP version).
--non-recursive  \tIf specified and the input parameter is a directory, then
                 \tonly files in the specified directory will be converted.


HELP;
    }

}
