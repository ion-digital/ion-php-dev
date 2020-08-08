<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs;

/**
 * Description of VcsProvider
 *
 * @author Justus.Meyer
 */

use \Exception;

abstract class VcsProvider implements IVcsProvider {

    protected const MAX_CMD_LENGTH = 1500; // https://support.microsoft.com/en-us/help/830473/command-prompt-cmd-exe-command-line-string-limitation

    public function __construct() {

    }
    
    protected function exec(string $cmd, array &$output): int {
        
        $output = [];
        $ret = 0;
        
        exec($cmd, $output, $ret);
        
        return $ret;
    }
    
    protected function batchExec(string $primaryCmd, string $secondaryCmd = null, array $files = [], int $max = self::MAX_CMD_LENGTH): void {
        
        $batches = [];
        
        $batch = '';
        foreach($files as $file) {
            
            if(strlen($batch) + strlen($file) > static::MAX_CMD_LENGTH) {
                $batches[] = $batch;
                $batch = '';
            }
            
            $batch .= ' ' . $file;
        }
        
        if(strlen($batch) > 0) {
            $batches[] = $batch;
        }
        
        foreach($batches as $index => $batch) {
            
            $cmd = ($index === 0 ? $primaryCmd : ($secondaryCmd !== null ? $secondaryCmd : $primaryCmd));

            $batches[$index] = $cmd . ' ' . trim($batches[$index]);
        }
        
        
        $output = [];      
        
        foreach($batches as $index => $batch) {

            echo "Executing VCS command" . ($index === 0 ? '' : "(batch #$index)") . ":\n\n$batch\n\n";
            
            $ret = 0;
            exec($batch, $output, $ret);            

        }
        
        //return ($ret === 0 ? ToolExitCodes::FILES_COMMITTED : ToolExitCodes::FILES_NOT_COMMITTED);        
        
        return;
    }


}
