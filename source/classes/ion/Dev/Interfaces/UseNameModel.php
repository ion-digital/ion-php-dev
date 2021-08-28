<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Interfaces;

/**
 * Description of NameModel
 *
 * @author Justus
 */
class UseNameModel extends NameModel {
    
    private $references = 0;

    public function __construct(NameModel $name) {
    
        $this->setName($name->getName());
        $this->setNamespaceParts($name->getNamespaceParts());
    }

    public function increaseReferences(): self {
        
        $this->references++;
        return $this;
    }
    
    public function getReferences(): int {
        
        return $this->references;
    }
    
    public function hasReferences(): bool {
        
        return ($this->references > 0);
    }
    
    public function toString(): string {
        
        return "";
    }
}
