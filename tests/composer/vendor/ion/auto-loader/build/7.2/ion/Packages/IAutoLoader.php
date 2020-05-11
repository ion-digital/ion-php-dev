<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages;

/**
 *
 * @author Justus
 */
use ion\IPackage;

interface IAutoLoader
{
    static function createCacheFilename(string $deploymentId) : string;
    
    static function createDeploymentId(IPackage $package, string $includePath) : string;
    
    static function create(IPackage $package, string $includePath) : IAutoLoader;
    
    function load(string $className) : bool;
    
    function getPackage() : IPackage;
    
    function getIncludePath() : string;
    
    //    function hasCacheEntry(string $className): bool;
    //
    //    function getCacheEntry(string $className): ?string;
    //
    function saveCache() : void;
    
    function loadCache() : bool;

}