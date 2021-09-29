<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion\Dev\Vcs;

/**
 * Description of Transformation
 *
 * @author Justus.Meyer
 */

use \ion\Dev\Tool;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Exception;
use \ion\Dev\Vcs\Providers\HgProvider;
use \ion\Dev\Vcs\Providers\GitProvider;
use \ion\ISemVer;
use \ion\SemVer;

class VcsTool extends Tool {

    use TVersion;
    
    public function execute(): int {
     
        $operation = $this->getArgs()->operation;
        $paths = (isset($this->getArgs()->paths) ? $this->getArgs()->paths : null);               
        $message = (isset($this->getArgs()->message) ? $this->getArgs()->message : "Package commit for version '{$this->loadVersion()}'");
        $check = (isset($this->getArgs()->check) ? $this->getArgs()->check : null);
        $update = (isset($this->getArgs()->update) ? $this->getArgs()->update : null);
        $output = (isset($this->getArgs()->output) ? $this->getArgs()->output : null);
        $prepend = (isset($this->getArgs()->prepend) ? $this->getArgs()->prepend : null);
        $append = (isset($this->getArgs()->append) ? $this->getArgs()->append : null);
        
        $provider = HgProvider::check(getcwd());
        
        if($provider === null) {
            
           $provider = GitProvider::check(getcwd()); 
        }    
        
        if($provider === null) {
            
            throw new Exception("No version control repository has been found in this project.");
        }
            
        switch(strtolower($operation)) {

            case 'pull': {

                if($provider->pull()) {
                    
                    $this->writeln("Files successfully pulled from remote.");
                    break;
                }
                
                throw new Exception("Files failed to be pulled from remote.");
            }            
            
            case 'add': {

                $result = $provider->add($paths);
                
                if(count($result) > 0) {
                    
                    foreach($result as $file) {
                        
                        $this->writeln("'{$file}' added to repository.");
                    }
                    break;
                }
                
                $this->writeln("No files found to be added to repository.");
                
                break;
                //throw new Exception("Files could not be added to repository.");
            }

            case 'commit': {

                $result = $provider->commit($message, $paths);
                
                if(count($result) > 0) {
                    
                    foreach($result as $file) {
                        
                        $this->writeln("'{$file}' committed to repository.");
                    }
                    break;
                }
                
                $this->writeln("No files found to be committed to repository.");
                
                break;
                //throw new Exception("Files could not be committed to the repository.");
                
            }

            case 'push': {

                if($provider->push()) {
                    
                    $this->writeln("Files successfully pushed to remote.");
                    break;
                }
                
                throw new Exception("Files failed to be pushed to remote.");
            }



            case 'version': {

                if($check === null) {
                    
                    $check = 'auto';
                }                
                
                $tags = $provider->getTags();
                $currentVersion = null;
                $newVersion = $this->loadVersion($check);
                
                foreach($tags as $tag) {
                    
                    $tmp = SemVer::parse($tag);
                    
                    if($tmp !== null) {
                        
                        $currentVersion = $tmp;
                        break;
                    }
                }

                if($currentVersion !== null) {
                    
                    if($output === false) {
                        
                        $this->writeln("Current version detected: {$currentVersion->toString()}");
                    }
                } else {
                    
                    if($newVersion === null) {
                        
                        $newVersion = SemVer::create(0, 0, 1);                    
                    }
                    
                    //$currentVersion = SemVer::create(0, 0, 0);                    
                    $currentVersion = null;
                    
                    if($output === false) {
                        
                        $this->writeln("No current version detected.");
                        
                    } 
                }


                if(($newVersion !== null && $currentVersion !== null && $newVersion->isHigherThan($currentVersion)) || ($currentVersion === null)) {

                    if($output === false) {
                    
                        $this->writeln("Version change detected (" . ($currentVersion === null ? 'none' : $currentVersion) . " -> {$newVersion}).");
                        
                    } else {
                        
                        $this->write(($prepend === null ? '' : $prepend) . $this->getVersion($output, $newVersion) . ($append === null ? '' : $append));
                    }
                    
                    if($update === false) {

                        return 1;
                    }
                    
                    if($provider->tag($newVersion->toVcsTag())) {
                    
                        if($output === false) {

                            $this->writeln("Version updated to {$newVersion}.");
                        } 
                        
                    } else {
                        
                        throw new Exception("Could not update VCS tag.");
                    }
                    
                    return 0;
                }                 
                
                if($output === false) {
                
                    $this->writeln("No version change detected.");                
                    
                } else {
                    
                    $this->write(($prepend === null ? '' : $prepend) . $this->getVersion($output, $currentVersion) . ($append === null ? '' : $append));             
                }
                
                return -1;
            }            

        }
        
        return 0;
    }
    
    private function getVersion(string $output = null, SemVerInterface $version = null): string {
        
        if($version === null) {
            
            return '';
        }
        
        if($output === null) {
            
            return $version->toString();
        }
        
        switch($output) {
            case 'major': {

                return $version->getMajor(); 
            }
            case 'minor': {

                return $version->getMinor(); 
            }
            case 'patch': {
                
                return $version->getPatch();                
            }                                
        }        
        
        throw new Exception("Invalid version output option.");
    }
    
//    protected function updateVersion(SemVerInterface $newVersion, array $inputs): int {
//
//        //$semVer = SemVer::parse($this->getLatestVersion());
//
//        $output = [];
//        $cmd = null;
//
//        if ($this->isHg()) {
//            $cmd = self::HG_PUT_TAG_CMD . ' ' . $newVersion->toVcsTag();
//            
//        } else if ($this->isGit()) {
//            
//            throw new ToolException('GIT has not been enabled yet.');
//            
//            $cmd = self::GIT_PUT_TAG_CMD . ' ' . $newVersion->toVcsTag();
//        }
//
//        
//        
//        if ($cmd !== null) {
//            exec($cmd, $output);
//            Tool::out("'$cmd' executed.\n");
//            return ToolExitCodes::VERSION_UPDATED;
//        } 
//        
//        Tool::out("Could not determine VCS tag update command.\n");
//        
//        return ToolExitCodes::VERSION_NOT_UPDATED;                
//    }           
//    
//    protected function checkToUpdateVersion(SemVerInterface $version, array $inputs): bool {
//        
//        $current = $this->getLatestVersion($inputs);
//        
//        if($current === null) {
//            return true;
//        }
//        
//        if($version->isHigherThan($current)) {
//            return true;
//        }
//        
//        return false;
//    }    
//    
//
//   protected function getLatestVersion(array $inputs): ?SemVerInterface {
//
//        $output = [];
//        $cmd = null;
//
//        if ($this->isHg()) {
//            
//            $cmd = self::HG_GET_TAG_CMD;
//            
//        } else if ($this->isGit()) {
//            
//            throw new ToolException('GIT has not been enabled yet.');
//            
//            $cmd = self::GIT_GET_TAG_CMD;
//        }
//
//        if ($cmd !== null) {
//            
//            exec($cmd, $output);
//            return SemVer::parse(trim(join("\n", $output)));
//            
//        } else {
//            Tool::out("Could not determine VCS tag retrieval command.\n");
//        }
//
//        return null;
//    }    
    
}
