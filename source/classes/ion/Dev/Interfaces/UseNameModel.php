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

    public function __construct(NameModel $name, array $namespaceParts = null) {
    
        $this->setName($name->getName());
        $this->setAbsolute(true);
        $this->setNamespaceParts($namespaceParts === null ? $name->getNamespaceParts() : []);
    }

    public function increaseReferences(): self {
        
        $this->references++;
        return $this;
    }
    
    public function getReferenceCount(): int {
        
        return $this->references;
    }
    
    public function clearReferencesCount(): void {
        
        $this->references = 0;
        return;
    }
    
    public function hasReferences(): bool {
        
        return ($this->references > 0);
    }
    
    public function toString(): string {
        
        return "use " . parent::getFullName() . ";"; // {$this->getReferenceCount()}";
    }
//    
//    public function __toString() {
//        
//        return $this->toString();
//    }
}
