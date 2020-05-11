<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Vcs;

/**
 *
 * @author Justus.Meyer
 */
interface IVcsProvider {
    
    static function check(string $path = null): ?self;
    
    function add(array $files = []): array;
    
    function commit(string $message = null, array $files = []): array;
    
    function pull(): bool;
    
    function push(): bool;
    
    function getTags(bool $sortDescending = true): array;
    
    function getStatus(array $paths = []): array;

    function tag(string $tag): bool;
    
}
