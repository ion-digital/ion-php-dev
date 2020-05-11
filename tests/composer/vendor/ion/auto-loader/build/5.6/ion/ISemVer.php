<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;


interface ISemVer
{
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    
    static function parse($string);
    
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    
    static function parsePackageJson($data);
    
    /**
     * method
     * 
     * 
     * @return ?ISemVer
     */
    
    static function parseComposerJson($data);
    
    /**
     * method
     * 
     * 
     * @return ISemVer
     */
    
    static function create($major = 0, $minor = 0, $patch = 1, $preRelease = null, array $metaData = null);
    
    /**
     * Instance constructor.
     * 
     * @param int $major The major version component.
     * @param int $minor The minor version component.
     * @param int $patch The patch version component.
     * 
     * @return void
     */
    
    function __construct($major = 0, $minor = 0, $patch = 1, $preRelease = null, array $metaData = []);
    
    /**
     * Get the major version component.
     * 
     * @return int Returns the major version component.
     */
    
    function getMajor();
    
    /**
     * Get the minor version component.
     * 
     * @return int Returns the minor version component.
     */
    
    function getMinor();
    
    /**
     * Get the patch version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getPatch();
    
    /**
     * Get the pre-release version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getPreRelease();
    
    /**
     * Get the meta-data version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getMetaData();
    
    /**
     * Get the version as a string.
     * 
     * @return string Return the version as a string.
     */
    
    function toString();
    
    /**
     * Get the version as a VCS tag (e.g: v.0.0.0)
     * 
     * @return string The version as a VCS tag.
     */
    
    function toVcsTag();
    
    /**
     * Get the version as an array.
     * 
     * @return array Return the version as an array.
     */
    
    function toArray();
    
    /**
     * Checks to see if this version is higher than the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is higher, __false__ if not.
     */
    
    function isHigherThan(ISemVer $semVer);
    
    /**
     * Checks to see if this version is lower than the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is lower, __false__ if not.
     */
    
    function isLowerThan(ISemVer $semVer);
    
    /**
     * Checks to see if this version is equal to the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is equal, __false__ if not.
     */
    
    function isEqualTo(ISemVer $semVer);

}