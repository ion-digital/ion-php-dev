<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs\Providers;

/**
 * Description of HgProvider
 *
 * @author Justus.Meyer
 */

use \ion\Dev\Vcs\IVcsProvider;
use \ion\Dev\Vcs\VcsProvider;
use \Exception;

class HgProvider extends VcsProvider {
        
    const ADD_CMD = 'hg add';
    const COMMIT_CMD = 'hg commit --addremove -m';
    const AMEND_CMD = 'hg commit --addremove --amend -m';
    const PULL_CMD = 'hg pull';
    const PUSH_CMD = 'hg push -b default';
    const TAGS_CMD = 'hg log --template "{tags}\n"';
    const TAG_CMD = 'hg tag';
    const STATUS_CMD = 'hg status -A -q -u'; 
    
    final public static function check(string $path = null): ?IVcsProvider {
        
        if($path === null) {
            
            $path = getcwd();
        }
                
        if(is_dir($path . DIRECTORY_SEPARATOR . '.hg')) {
            
            return new static();
        }

        return null;
    }    
    
    public function __construct() {
        
        parent::__construct();
    }
    
    public function commit(string $message = null, array $paths = []): array {

        $status = $this->getStatus($paths);
        
        $files = [];
        
        foreach($status as $flag => $tmp) {

            if($flag === 'M' || $flag === 'A' || $flag === 'R' || $flag === '!') {
                
                $files = array_merge($files, $tmp);
            }
        }        

        $msg = '';
        if($message !== null) {
            
            $msg = $message;
        }
        
        
        $this->batchExec(self::COMMIT_CMD . ' "' . $message . '"', self::AMEND_CMD . ' "' . $message . '"', $files);
        
        return $files;
    }
    
    public function pull(): bool {
        
        $output = [];
        $return = 0;
        
        exec(static::PULL_CMD, $output, $return);
        
        return ($return === 0);
    }
    
    public function push(): bool {
        
        $output = [];
        $return = 0;
        
        exec(static::PUSH_CMD, $output, $return);
        
        return ($return === 0);
    }
    
    public function getTags(bool $sortDescending = true): array {
        
        $output = [];
        $return = 0;
        
        exec(static::TAGS_CMD, $output, $return);

        if($sortDescending) {
            
            return $output;
        }
        
        return array_reverse($output);
    }

    public function add(array $paths = []): array {
        
        $status = $this->getStatus($paths);
        
        $files = [];
        
        foreach($status as $flag => $tmp) {

            if($flag === '??') {
                
                $files = array_merge($files, $tmp);
            }
        }        

        $this->batchExec(static::ADD_CMD, null, $files);       
        
        return $files;
    }    
    
    public function getStatus(array $paths = []): array {
        
        $output = [];
        $return = 0;
        
        $cmd = static::STATUS_CMD . (count($paths) > 0 ? ' ' . implode(' ', $paths) : '');
        
        exec($cmd, $output, $return);
        
        if($return === 0) {

            $status = [];
            
            foreach($output as $tmp1) {
                
                $tmp2 = explode(' ', trim($tmp1), 2); 
                
//                print_r($tmp2);
//                exit;
                
                if(!array_key_exists($tmp2[0], $status)) {
                    
                    $status[$tmp2[0]] = [];
                }
  
                if(substr(trim($tmp2[1]), 0, strlen('vendor/')) === 'vendor/') {
                    
                    continue;
                }
                
                $status[$tmp2[0]][] = trim($tmp2[1]);
            }
            
            return $status;
        }
                
        throw new Exception("'{$cmd}' returned with code $return.");
    }
    
    public function tag(string $tag): bool {
        
        $output = [];
        $return = 0;
        
        //die(static::TAG_CMD . " \"{$tag}\"");
        
        exec(static::TAG_CMD . " \"{$tag}\"", $output, $return);
        
        return ($return === 0);
    }    
}
