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
    /**
     * method
     * 
     * 
     * @return string
     */
    
    static function createCacheFilename(string $deploymentId) : string;
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    static function createDeploymentId(IPackage $package, string $includePath) : string;
    
    /**
     * method
     * 
     * 
     * @return IAutoLoader
     */
    
    static function create(IPackage $package, string $includePath) : IAutoLoader;
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    function load(string $className) : bool;
    
    /**
     * method
     * 
     * @return IPackage
     */
    
    function getPackage() : IPackage;
    
    /**
     * method
     * 
     * @return string
     */
    
    function getIncludePath() : string;
    
    //    function hasCacheEntry(string $className): bool;
    //
    //    function getCacheEntry(string $className): ?string;
    //
    /**
     * method
     * 
     * @return void
     */
    
    function saveCache() : void;
    
    /**
     * method
     * 
     * @return bool
     */
    
    function loadCache() : bool;

}