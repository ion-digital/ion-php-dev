<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation\Generators;

/**
 *
 * @author Justus
 */

use \ion\Dev\Documentation\DocumentationGenerator;
use \Exception;
use Symfony\Component\Console\Output\OutputInterface;

class PhpDocDocumentationGenerator extends DocumentationGenerator {
    
    private const KEY = "phpdoc";
    private const URI = "https://phpdoc.org/phpDocumentor.phar";   
    private const CONFIG_FILENAME = "phpdoc.xml";
    
    public static function getClassKey(): string {
        
        return self::KEY;
    }
    
    public function getUri(): string {
  
        return self::URI;
    }    
    
    public function getFilename(): ?string {
        
        return pathinfo($this->getUri())['basename'];
    }        
    
    public function execute(OutputInterface $output): int {
        
        $output->writeln("Objects to scan: \n\n\t -> " . implode("\t -> ", array_keys($this->getInputObjects())) . "\n");
        
        $output->write("Creating configuration file ... ");
        
        $this->writeConfig();
        
        $output->writeln("Done.");
        
        $cmdOutput = [];
        $cmdResult = 0;
        
        $output->writeln("php " . $this->getPath() . " --config " . self::CONFIG_FILENAME);
        
        exec($this->getPath() . " --config " . self::CONFIG_FILENAME, $cmdOutput, $cmdResult);
        
        $output->write($cmdOutput);
        
        return $cmdResult;
    }
    
    protected function writeConfig(): void {
        
        
        
        file_put_contents(self::CONFIG_FILENAME, trim(<<<XML
                
<?xml version="1.0" encoding="UTF-8" ?>
<phpdocumentor configVersion="3.0">
  <paths>
    <output>build/docs</output>
    <!--Optional:-->
    <cache>string</cache>
  </paths>
  <!--Zero or more repetitions:-->
  <version number="3.0">
    <!--Optional:-->
    <folder>latest</folder>
    <!--Zero or more repetitions:-->
    <api format="php">
      <source dsn=".">
        <!--1 or more repetitions:-->
        <path>src</path>
      </source>
      <!--Optional:-->
      <output>api</output>
      <!--Optional:-->
      <ignore hidden="true" symlinks="true">
        <!--1 or more repetitions:-->
        <path>tests/**/*</path>
      </ignore>
      <!--Optional:-->
      <extensions>
        <!--1 or more repetitions:-->
        <extension>php</extension>
      </extensions>
      <!--Optional:-->
      <visibility>private</visibility>
      <!--Optional:-->
      <default-package-name>MyPackage</default-package-name>
      <!--Optional:-->
      <include-source>true</include-source>
      <!--Optional:-->
      <markers>
        <!--1 or more repetitions:-->
        <marker>TODO</marker>
        <marker>FIXME</marker>
      </markers>
    </api>
    <!--Zero or more repetitions:-->
    <guide format="rst">
      <source dsn=".">
        <!--1 or more repetitions:-->
        <path>support/docs</path>
      </source>
      <!--Optional:-->
      <output>docs</output>
    </guide>
  </version>
  <!--Zero or more repetitions:-->
  <setting name="string" value="string"/>
  <!--Zero or more repetitions:-->
  <template name="string" location="string">
    <!--Zero or more repetitions:-->
    <parameter name="string" value="string"/>
  </template>
</phpdocumentor>
                
XML
        ) . "\n");
    }
    
    
}