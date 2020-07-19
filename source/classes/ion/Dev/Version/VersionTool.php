<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Version;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */
use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;
use \ion\ISemVer;
use \ion\SemVer;

// cls && echo "VERSION" | composer regex match "/x/"

class VersionTool extends Tool {

    use TVersion;
    
    private $output = null;
    private $version = null;
    private $print = null;
    private $check = null;
    private $checkReturn = null;
    private $checkSwap = null;
    private $increment = null;
    private $update = null;
    private $release = null;
    private $build = null;
    
    public function __construct(
    
            string $version,
            bool $print = null,
            bool $checkReturn = null,
            bool $checkSwap = null,
            string $check = null,
            string $increment = null,
            string $update = null,
            string $release = null,
            array $build = null,
            InputInterface $input = null,
            OutputInterface $output = null
    ) {
        
        $this->version = $version;
        $this->print = $print;
        $this->check = $check;
        $this->checkReturn = $checkReturn;
        $this->checkSwap = $checkSwap;
        $this->increment = $increment;
        $this->update = $update;
        $this->release = $release;
        $this->build = $build;
        $this->output = $output;
        
        parent::__construct(null, $input, $output);
    }
    
    public function execute(): int {

        $return = 0;
        
        $version = $this->loadVersion($this->version);
        
        $pkgVersion = null;
        
        if($this->check !== null) {
            
            $source = "";
            $pkgVersion = $this->loadVersion($this->check);
            
            //var_dump($this->print);
            
            $vars = [];
            
            if(!$this->checkSwap) {
                
                $vars[] = [ 'version' => $pkgVersion, 'label' => "version.json / composer.json" ];
                $vars[] = [ 'version' => $version, 'label' => "specified" ];
                
            } else {
                
                $vars[] = [ 'version' => $version, 'label' => "specified" ];
                $vars[] = [ 'version' => $pkgVersion, 'label' => "version.json / composer.json" ];
            }
            
            if($vars[0]['version']->isHigherThan($vars[1]['version'])) {
                
            
                if($this->print === false) {
                    
                    $this->writeln("A version change has been detected.");
                    $this->writeln("{$vars[1]['version']} ({$vars[1]['label']}) < {$vars[0]['version']} ({$vars[0]['label']}).");
                }
                
                $return = ($this->checkReturn ? 1 : 0);
                
            } else if($vars[0]['version']->isLowerThan($vars[1]['version'])) {

                if($this->print === false) {

                    $this->writeln("No version change has been detected.");
                    $this->writeln("{$vars[1]['version']} ({$vars[1]['label']}) > {$vars[0]['version']} ({$vars[0]['label']}).");
                }                
                
                $return = ($this->checkReturn ? -1 : 1);
                
            } else {

                if($this->print === false) {
                
                    $this->writeln("No version change has been detected.");
                    $this->writeln("{$vars[1]['version']} ({$vars[1]['label']}) == {$vars[0]['version']} ({$vars[0]['label']}).");
                }
                
                $return = ($this->checkReturn ? 0 : 1);
            }
            
            if($this->update === null && !$this->print) {
                
                $this->writeln("Exiting with code: $return");
                return $return;
            }
        }
        
        if(is_countable($this->build)) {
            
            $version = new SemVer(
                    
                $version->getMajor(),
                $version->getMinor(),
                $version->getPatch(),
                $version->getRelease(),
                $this->build
            );
        }
        
        if($this->release !== null) {
            
            $version = new SemVer(
                    
                $version->getMajor(),
                $version->getMinor(),
                $version->getPatch(),
                (empty($this->release) ? null : $this->release),
                $version->getBuildData()
            );      
        }
        
        if($this->increment !== null) {
            
            $version = new SemVer(
                    
                $version->getMajor() + (strtolower($this->increment) === 'major' ? 1 : 0),
                $version->getMinor() + (strtolower($this->increment) === 'minor' ? 1 : 0),
                $version->getPatch() + (strtolower($this->increment) === 'patch' || empty($this->increment) ? 1 : 0),
                $version->getRelease(),
                $version->getBuildData()
            );            
        }
        
        if($this->print === true) {
            
            if($version !== null) {
                
                $this->write($version);
            }            

            if($this->update === null) {
                
                return $return;
            }            
        }            
        
        if($this->update !== null) {
            
            $expectedReturn = ($this->checkReturn ? 1 : 0);
            
            if($this->check !== null && ($return !== $expectedReturn)) {

                return 2;
            }
            
            //$targetVersion = ($this->check === null ? $version : $pkgVersion);
            
            $targetVersion = $version;
            
            $return = $this->saveVersion($targetVersion, $this->update, $this->print);
            
            if($this->print) {
                
                return $return;
            }
            
            if($return === $expectedReturn) {

                $this->writeln("Version updated to '{$targetVersion}'.");
                
            } else {
                
                throw new Exception("Could not update version.");
            }
            
            return $return;
        }
        
        if($this->print === false) {
            
            $this->writeln("Nothing to do!");
        }            
        
        return $return;
    }
    
    protected function applyVersion(ISemVer $semVer = null): ?ISemVer {
        
        return $semVer;
    }
    
    public function print(): void {
        
        
    }
    
    public function check(): int {
        
    }
    
    public function update(): void {
        
    }
    
    public function increment(bool $patch = true, bool $minor = false, bool $major = false): ?ISemVer {
        
        
    }
}