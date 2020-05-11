<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;


interface ISemVer
{
    static function parse(string $string) : ?ISemVer;
    
    static function parsePackageJson(string $data) : ?ISemVer;
    
    static function parseComposerJson(string $data) : ?ISemVer;
    
    static function create(int $major = 0, int $minor = 0, int $patch = 1, string $preRelease = null, array $metaData = null) : ISemVer;
    
    /**
     * Instance constructor.
     * 
     * @param int $major The major version component.
     * @param int $minor The minor version component.
     * @param int $patch The patch version component.
     * 
     * @return void
     */
    
    function __construct(int $major = 0, int $minor = 0, int $patch = 1, string $preRelease = null, array $metaData = []);
    
    /**
     * Get the major version component.
     * 
     * @return int Returns the major version component.
     */
    
    function getMajor() : int;
    
    /**
     * Get the minor version component.
     * 
     * @return int Returns the minor version component.
     */
    
    function getMinor() : int;
    
    /**
     * Get the patch version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getPatch() : int;
    
    /**
     * Get the pre-release version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getPreRelease() : ?string;
    
    /**
     * Get the meta-data version component.
     * 
     * @return int Returns the patch version component.
     */
    
    function getMetaData() : array;
    
    /**
     * Get the version as a string.
     * 
     * @return string Return the version as a string.
     */
    
    function toString() : string;
    
    /**
     * Get the version as a VCS tag (e.g: v.0.0.0)
     * 
     * @return string The version as a VCS tag.
     */
    
    function toVcsTag() : string;
    
    /**
     * Get the version as an array.
     * 
     * @return array Return the version as an array.
     */
    
    function toArray() : array;
    
    /**
     * Checks to see if this version is higher than the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is higher, __false__ if not.
     */
    
    function isHigherThan(ISemVer $semVer) : bool;
    
    /**
     * Checks to see if this version is lower than the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is lower, __false__ if not.
     */
    
    function isLowerThan(ISemVer $semVer) : bool;
    
    /**
     * Checks to see if this version is equal to the specified version.
     * 
     * @param ISemVer $semVer The specified version to check.
     * 
     * @return bool Returns __true__ if the version is equal, __false__ if not.
     */
    
    function isEqualTo(ISemVer $semVer) : bool;

}