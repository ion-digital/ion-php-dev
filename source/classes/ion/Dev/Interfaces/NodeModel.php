<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of DocumentModel
 *
 * @author Justus
 */
abstract class NodeModel {
    
    protected static function indent(string $s, int $indents = 4, bool $tabs = false): string {
        
        $lines = explode("\n", $s);
        
        $result = [];
        
        foreach($lines as $line) {
            
            $tmp = "";
            
            if(strlen($line) > 0) {

                for($cnt = 0; $cnt < $indents; $cnt++) {

                    $tmp .= $tabs ? "\t" : " ";
                }
            }
            
            //$result[] = $tmp . ($trimLines ? trim($line) : $line);
            $result[] = $tmp . $line;
        }
        
        return implode("\n", $result);
    }        
    
    protected static function trim(string $s, int $indents = 0, int $indentStart = 0, bool $tabs = false): string {
        
        $lines = explode("\n", $s);
        
        $result = [];
        
        foreach($lines as $index => $line) {
            
            $tmp = "";
            
            if($indents > 0 && $index >= $indentStart) {
                
                for($i = 0; $i < $indents; $i++) {
                    
                    $tmp .= $tabs ? "\t" : " ";
                }
            }
            
            $result[] = $tmp . trim($line);            
            
        }
        
        return implode("\n", $result);
    }
    
    private $doc;
    
    public function __construct(string $doc = null) {
        
        $this->doc = $doc;
    }
    
    public function setDoc(string $doc = null): self {
        
        $this->doc = $doc;
        return $this;
    }
    
    public function getDoc(bool $trim = true): ?string {
        
        if($this->doc === null) {
            
            return null;
        }
        
        if($trim) {
            
            return static::trim($this->doc, 1, 1, false);
        }
        
        return $this->doc;
    }
    
    public function hasDoc(): bool {
        
        return !empty($this->getDoc());
    }
    
    public abstract function toString(): string;
    
    public function __toString() {
        
        return $this->toString();
    }
}
