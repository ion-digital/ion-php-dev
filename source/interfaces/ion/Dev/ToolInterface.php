<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev;

/**
 *
 * @author Justus
 */
interface ToolInterface {
    
    static function create(\stdClass $args): self;
    
    function execute(): int;
}