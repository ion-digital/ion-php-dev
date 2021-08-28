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
    
    private $doc;
    
    public function __construct(string $doc = null) {
        
        $this->doc = $doc;
    }
    
    public function setDoc(string $doc = null): self {
        
        $this->doc = $doc;
        return $this;
    }
    
    public function getDoc(): ?string {
        
        return $this->doc;
    }
    
    public abstract function toString(): string;
    
    public function __toString() {
        
        return $this->toString();
    }
}
