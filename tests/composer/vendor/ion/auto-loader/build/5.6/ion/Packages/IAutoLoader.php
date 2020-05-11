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
    
    static function createCacheFilename($deploymentId);
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    static function createDeploymentId(IPackage $package, $includePath);
    
    /**
     * method
     * 
     * 
     * @return IAutoLoader
     */
    
    static function create(IPackage $package, $includePath);
    
    /**
     * method
     * 
     * 
     * @return bool
     */
    
    function load($className);
    
    /**
     * method
     * 
     * @return IPackage
     */
    
    function getPackage();
    
    /**
     * method
     * 
     * @return string
     */
    
    function getIncludePath();
    
    //    function hasCacheEntry(string $className): bool;
    //
    //    function getCacheEntry(string $className): ?string;
    //
    /**
     * method
     * 
     * @return void
     */
    
    function saveCache();
    
    /**
     * method
     * 
     * @return bool
     */
    
    function loadCache();

}