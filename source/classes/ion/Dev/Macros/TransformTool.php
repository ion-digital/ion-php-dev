<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Macros;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use \Exception;
//use \ion\Dev\Macros\Fixture;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class TransformTool extends Tool {

    public function execute(): int {

        $cwd = realpath(getcwd()) . DIRECTORY_SEPARATOR;
        $this->writeln("Current working directory: '{$cwd}'");

        $self = $this;
        
        $ret = (function() use ($self) {
            
                    $fixtureFile = realpath($self->getArgs()->fixtures);

                    if (empty($fixtureFile)) {

                        $this->writeln("Could not locate fixtures file: '{$self->getArgs()->fixtures}'");
                        return -1;
                    }

                    $self->writeln("Root fixtures file found: '{$fixtureFile}'");
                    
                    Fixture::load($fixtureFile, function(string $path) use ($self) {

                                $self->writeln("Loaded fixture at '{$path}'.");
                            }, function(string $path, string $message = null) use ($self) {

                                throw new Exception("'Could not load fixture at '{$path}'" . ($message !== null ? " ($message)." : "."));
                            }, false);
                    
                    if ($self->getArgs()->operation === 'validate') {

                        // Fixtures have already been validated!

                        $self->writeln("Fixtures validated.");

                        return 0;
                    }

                    if ($self->getArgs()->operation === 'generate') {                                           
                        
                        return $self->generateFixtures(Fixture::getFixtures());
                    }

                    return 0;
                })();

        chdir($cwd);

        return $ret;
    }

    protected function generateFixtures(array &$fixtures): int {

        try {

            foreach ($fixtures as $name => $fixture) {

                if(!$fixture->getSuppressOutput()) {

                    $this->write("Processing macro '$name' ... ");

                    $result = $fixture->generate();

                    if (count($result) === 0) {

                        $this->writeln('OK');
                        continue;
                    }

                    if (count($result) === 1) {

                        $this->writeln($result[0]);
                        continue;
                    }

                    $this->writeln("->");
                    foreach ($result as $ln) {

                        $this->writeln("\t" . $ln);
                    }
                }
            }
        } catch (Exception $ex) {

            $this->writeln("{$ex->getMessage()}");
            return -1;
        }

        return 0;
    }

    //protected function 
}
