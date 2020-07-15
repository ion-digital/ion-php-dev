<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs\Providers;

/**
 * Description of Git
 *
 * @author Justus.Meyer
 */

use \ion\Dev\Vcs\IVcsProvider;
use \ion\Dev\Vcs\VcsProvider;
use \Exception;

class GitProvider extends VcsProvider {
    
    //
    const ADD_CMD = 'git add';
    const COMMIT_CMD = 'git commit'; //-a
    const AMEND_CMD = 'git commit --amend --no-edit';
    const PULL_CMD = 'git pull';
    const PUSH_CMD = 'git push --follow-tags';
    const TAGS_CMD = 'git describe --abbrev=0'; // https://stackoverflow.com/questions/4277773/how-to-get-latest-tag-name
    const TAG_CMD = 'git tag -a';
    const STATUS_CMD = 'git status -s';    
    
    final public static function check(string $path = null): ?IVcsProvider {
        
        if($path === null) {
            
            $path = getcwd();
        }
        
        if(is_dir($path . DIRECTORY_SEPARATOR . '.git')) {
            
            return new static();
        }     
        
        return null;
    }
    
    public function __construct() {
        
        parent::__construct();
    }    
    
    
    public function add(array $paths = []): array {
        
        $status = $this->getStatus($paths);
        
        $files = [];
        
        foreach($status as $flag => $tmp) {

            if($flag === '??') {
                
                $files = array_merge($files, $tmp);
            }
        }        
  
        if($paths === []) {
                        
            $output = [];
            
            $this->exec(self::ADD_CMD . ' .', $output);
            return $files;
        }        
        
        if($this->batchExec(static::ADD_CMD, null, $files) === 0) {
            
            return $files;
        }
        
        return [];
    }    
    
    public function commit(string $message = null, array $paths = []): array {
        
        $status = $this->getStatus($paths);
        
        $files = [];
        
        foreach($status as $flag => $tmp) {

            //if($flag === 'M' || $flag === 'A' || $flag === 'D' || $flag === 'R' || $flag === 'C' || $flag === 'U') {
            
            if(!empty($flag) && ($flag !== '??') && ($flag !== '!!')  && ($flag !== '!')) {
                
                $files = array_merge($files, $tmp);
            }
        }        

        $msg = '';
        if($message !== null) {
            
            $msg = $message;
        }
        
//        print_r($status);
//        print_r($files);
//        exit;
        
        //die(self::COMMIT_CMD . (empty($msg) ? '' : " -m \"{$msg}\""));
        
        if($paths === []) {
                        
            $output = [];
            
            $this->exec(self::COMMIT_CMD . " -a" . (empty($msg) ? '' : " -m \"{$msg}\""), $output);
            return $files;
        }
        
        if($this->batchExec(self::COMMIT_CMD . (empty($msg) ? '' : " -m \"{$msg}\""), self::AMEND_CMD, $files)) {
            
            return $files;
        }
        
        return [];
    }
    
    public function pull(): bool {
        
        $output = [];

        $return = $this->exec(static::PULL_CMD, $output);
        
        return ($return === 0);
    }
    
    public function push(): bool {
        
        $output = [];
        
        $return = $this->exec(static::PUSH_CMD, $output);
        
        return ($return === 0);
    }
    
    public function getTags(bool $sortDescending = true): array {
        
        $output = [];
        $return = 0;
        
        $return = $this->exec(static::TAGS_CMD, $output);
        
        if($sortDescending) {
            
            return $output;
        }
        
        return array_reverse($output);
    }

    
    public function getStatus(array $paths = []): array {
        
        $output = [];
        $return = 0;
        
        $cmd = static::STATUS_CMD . (count($paths) > 0 ? ' ' . implode(' ', $paths) : '');

        $return = $this->exec($cmd, $output);
        
        if($return === 0) {

            $status = [];

            foreach($output as $tmp1) {
                
                $tmp2 = explode(' ', trim($tmp1), 2); 

                if(!array_key_exists(trim($tmp2[0]), $status)) {
                    
                    $status[trim($tmp2[0])] = [];
                }

                $fn = trim($tmp2[1]);
                
                if(strpos($fn, '->')) {
                    
                    $tmp3 = explode(' -> ', $fn, 2);
                    $fn = trim($tmp3[1]);
                    
                    $status[trim($tmp2[0])][] = trim($tmp3[0]);
                    $status[trim($tmp2[0])][] = trim($tmp3[1]);
                    
                } else {
                
                    $status[trim($tmp2[0])][] = $fn;
                }
            }
            
            return $status;
        }
                
        throw new Exception("'{$cmd}' returned with code $return.");
    }
    
    public function tag(string $tag): bool {
        
        $output = [];
        
        //die(static::TAG_CMD . " \"{$tag}\"");
        
        $return = $this->exec(static::TAG_CMD . " \"{$tag}\" -m \"Package version update.\"", $output);
        
        return ($return === 0);
    }        
}
