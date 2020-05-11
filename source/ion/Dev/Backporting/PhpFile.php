<?php

namespace ion\Dev\Backporting;

/**
 * Description of PhpFile
 *
 * @author Justus
 */
use \Exception;

class PhpFile extends PhpConversion {

    public function __construct(string $file, Converter $converter) {

        parent::__construct($file, $converter);
    }

    public function convert(string $target): void {

//        $sourceVersion = join('.', $this->getConverter()->getSourceVersion());
//        $targetVersion = join('.', $this->getConverter()->getTargetVersion());

        echo "Converting '" . $this->getPath() . "' -> '" . $target . "' ... ";

        try {

            if (file_exists($this->getPath()) === true) {
                $inputFileContent = file_get_contents($this->getPath());
                file_put_contents($target, $this->getConverter()->process($inputFileContent));
            }

            echo "OK";
            
        } catch (Exception $ex) {
            echo "EXCEPTION: '" . $ex->getMessage() . "'";
        } catch (Throwable $t) {
            echo "ERROR: '" . $t->getMessage() . "'";
        }
        
        echo "\n";
    }

}
