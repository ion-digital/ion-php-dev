<?php

namespace ion\Dev\Backporting;

/**
 * Description of Converter
 *
 * @author Justus
 */
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use \PhpParser\PrettyPrinter\Standard;

class Converter implements IConverter {

    private static function getMajorVersion(array $version): int {

        if (count($version) >= 1) {
            return $version[0];
        }

        return 0;
    }

    private static function getMinorVersion(array $version): int {

        if (count($version) >= 2) {
            return $version[1];
        }

        return 0;
    }

    private $sourceVersion = null;
    private $targetVersion = null;

    public function __construct(array $sourceVersion, array $targetVersion) {

        $this->sourceVersion = $sourceVersion;
        $this->targetVersion = $targetVersion;
    }

    public function getSourceVersion(): array {
        return $this->sourceVersion;
    }

    public function getTargetVersion(): array {
        return $this->targetVersion;
    }

    public function process(string $content): string {

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        $ast = $parser->parse($content);

        $result = null;

        switch (static::getMajorVersion($this->getTargetVersion())) {

            // PHP 5.x

            case 5: {

                    switch (static::getMinorVersion($this->getTargetVersion())) {

                        // PHP 5.6

                        case 6: {
                                $result = Conversion::convertTo56($ast, static::getMajorVersion($this->getSourceVersion()), static::getMinorVersion($this->getSourceVersion()));
                            }

                        default: {
                                //TODO: throw exception
                            }
                    }
                };

            // PHP 7.x

            case 7: {

                    switch (static::getMinorVersion($this->getTargetVersion())) {

                        // PHP 7.2

                        case 2: {
                                $result = Conversion::convertTo72($ast, static::getMajorVersion($this->getSourceVersion()), static::getMinorVersion($this->getSourceVersion()));
                                break;
                            }        
                            
                       // PHP 7.1

                        case 1: {
                                $result = Conversion::convertTo71($ast, static::getMajorVersion($this->getSourceVersion()), static::getMinorVersion($this->getSourceVersion()));
                                break;
                            }                            
                        
                        // PHP 7.0

                        case 0: {
                                $result = Conversion::convertTo70($ast, static::getMajorVersion($this->getSourceVersion()), static::getMinorVersion($this->getSourceVersion()));
                                break;
                            }

                        default: {
                                //TODO: throw exception
                            }
                    }
                };
        }



        return "<?php\n" . (new PrettyPrinter())->prettyPrint($ast);
    }

}
