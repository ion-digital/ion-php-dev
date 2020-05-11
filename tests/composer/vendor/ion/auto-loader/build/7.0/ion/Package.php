<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

/**
 * Description of Package
 *
 * @author Justus
 */
use ion\ISemVer;
use ion\SemVer;
use ion\Packages\PackageException;
use ion\Packages\Adapters\Psr0Loader;
use ion\Packages\Adapters\Psr4Loader;

final class Package implements IPackage
{
    const PHP_VERSION_SEPARATOR = '.';
    const COMPOSER_FILENAME = 'composer.json';
    const ION_PACKAGE_VERSION_FILENAME = 'version.json';
    const ION_AUTOLOAD_CACHE = 'ION_AUTOLOAD_CACHE';
    const ION_AUTOLOAD_CACHE_DEBUG = 'ION_AUTOLOAD_CACHE_DEBUG';
    const ION_PACKAGE_IGNORE_VERSION = 'ION_PACKAGE_IGNORE_VERSION';
    const ION_PACKAGE_DEBUG = 'ION_PACKAGE_DEBUG';
    private static $instances = [];
    /**
     * method
     * 
     * 
     * @return IPackage
     */
    
    public static function create(string $vendor, string $project, array $developmentPaths, array $additionalPaths = null, string $projectRoot = null, ISemVer $version = null, bool $enableDebug = null, bool $enableCache = null, array $loaderClassNames = null) : IPackage
    {
        return new static($vendor, $project, $developmentPaths, $additionalPaths, $projectRoot, $version, $enableDebug, $enableCache, $loaderClassNames);
    }
    
    /**
     * method
     * 
     * 
     * @return ?string
     */
    
    public static function createSearchPath(IPackage $package, string $path)
    {
        $includePath = trim($package->getProjectRoot(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        //echo $includePath . "\n";
        if (DIRECTORY_SEPARATOR === '/') {
            $includePath = DIRECTORY_SEPARATOR . $includePath;
        }
        $includePath = realpath($includePath);
        return $includePath === false ? null : $includePath . DIRECTORY_SEPARATOR;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public static function getInstances() : array
    {
        return static::$instances;
    }
    
    /**
     * method
     * 
     * 
     * @return IPackage
     */
    
    public static function getInstance(string $name) : IPackage
    {
        return static::$instances[$name];
    }
    
    /**
     * method
     * 
     * 
     * @return void
     */
    
    protected static function destroyInstance(self $instance)
    {
        unset(static::$instances[$instance->getName()]);
    }
    
    /**
     * method
     * 
     * 
     * @return void
     */
    
    protected static function registerInstance(self $instance)
    {
        if ($instance->getVersion() !== null) {
            if (array_key_exists($instance->getName(), static::$instances) === true) {
                $tmp = static::$instances[$instance->getName()];
                if ($tmp->getVersion() !== null) {
                    if ($instance->getVersion()->isLowerThan($tmp->getVersion())) {
                        static::$instances[$instance->getName()]->destroy();
                    }
                }
            }
        }
        static::$instances[$instance->getName()] = $instance;
        return;
    }
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    public static function getCallingDirectory(int $back = 1) : string
    {
        $trace = debug_backtrace();
        if ($back > count($trace)) {
            $back = count($trace) - 1;
        }
        for ($i = 0; $i < $back; $i++) {
            array_shift($trace);
        }
        $trace = array_values($trace);
        return dirname($trace[array_search(__FUNCTION__, array_column($trace, 'function'))]['file']) . DIRECTORY_SEPARATOR;
    }
    
    private $vendor = null;
    private $project = null;
    private $version = null;
    private $name = null;
    private $projectRoot = null;
    private $includePaths = [];
    private $sourcePaths = null;
    private $searchPaths = [];
    private $hooks = [];
    private $loaders = [];
    private $enableCache = false;
    private $enableDebug = false;
    private $cache = [];
    /**
     * method
     * 
     * 
     * @return mixed
     */
    
    protected function __construct(string $vendor, string $project, array $developmentPaths, array $additionalPaths = null, string $projectRoot = null, ISemVer $version = null, bool $enableDebug = null, bool $enableCache = null, array $loaderClassNames = null)
    {
        $this->vendor = $vendor;
        $this->project = $project;
        $this->name = $vendor . '/' . $project;
        $this->sourcePaths = $developmentPaths;
        if ($projectRoot === null) {
            $this->projectRoot = static::getCallingDirectory();
        } else {
            $this->projectRoot = $projectRoot . DIRECTORY_SEPARATOR;
        }
        $this->enableCache = false;
        if ($enableCache === null) {
            if (defined(static::ION_AUTOLOAD_CACHE)) {
                $this->enableCache = (bool) constant(static::ION_AUTOLOAD_CACHE) === true;
            }
        } else {
            $this->enableCache = $enableCache;
        }
        $this->enableDebug = true;
        if ($enableDebug === null) {
            if (defined(static::ION_PACKAGE_DEBUG)) {
                $this->enableDebug = (bool) constant(static::ION_PACKAGE_DEBUG) === true;
            }
        } else {
            $this->enableDebug = $enableDebug;
        }
        $this->version = $version;
        if ($this->version === null) {
            $this->version = $this->loadVersion();
        }
        $this->includePaths = $additionalPaths;
        if ($this->includePaths === null) {
            $this->includePaths = [];
        }
        $tmpPaths = $this->includePaths;
        if ($this->enableDebug) {
            $tmpPaths = [];
            // Override if 'debug' is true
        }
        // Add the dev directories at the end
        $tmpPaths = array_merge($tmpPaths, $developmentPaths);
        $this->searchPaths = [];
        foreach ($tmpPaths as $path) {
            $includePath = static::createSearchPath($this, $path);
            if ($includePath !== null) {
                $this->searchPaths[] = $includePath;
                if ($loaderClassNames === null || is_array($loaderClassNames) && count($loaderClassNames) === 0) {
                    $psr0 = Psr0Loader::class;
                    $psr4 = Psr4Loader::class;
                    $this->loaders[] = $psr0::create($this, $includePath);
                    $this->loaders[] = $psr4::create($this, $includePath);
                } else {
                    foreach ($loaderClassNames as $loaderClassName) {
                        if (!class_exists($loaderClassName)) {
                            throw new PackageException("'{$loaderClassName}' does not exist and cannot be used as an auto-loader.");
                        }
                        $this->loaders[] = $loaderClassName::create($this, $includePath);
                    }
                }
            }
        }
        static::registerInstance($this);
        $this->registerLoaders();
    }
    
    /**
     * method
     * 
     * @return void
     */
    
    protected function registerLoaders()
    {
        if (count($this->getHooks()) === 0) {
            try {
                $self = $this;
                foreach ($this->loaders as $index => $loader) {
                    $this->hooks[] = function (string $className) use($index, $loader, $self) {
                        $loader->load($className);
                    };
                }
                foreach ($this->hooks as $hook) {
                    spl_autoload_register($hook, true, false);
                }
            } catch (Exception $ex) {
                throw $ex;
            }
        }
        return;
    }
    
    /**
     * method
     * 
     * @return void
     */
    
    public function destroy()
    {
        if (count($this->getHooks()) > 0) {
            foreach ($this->hooks as $hook) {
                spl_autoload_unregister($hook);
            }
            $this->hooksRegistered = false;
        }
        static::destroyInstance($this);
        return;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getHooks() : array
    {
        return $this->hooks;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getLoaders() : array
    {
        return $this->loaders;
    }
    
    /**
     * method
     * 
     * 
     * @return string
     */
    
    protected function getVendorRoot(string $includePath, int $phpMajorVersion = null, int $phpMinorVersion = null) : string
    {
        if ($phpMajorVersion !== null || $phpMajorVersion !== null && $phpMinorVersion !== null) {
            if ($phpMinorVersion !== null) {
                return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . static::PHP_VERSION_SEPARATOR . $phpMinorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
            }
            return $includePath . DIRECTORY_SEPARATOR . $phpMajorVersion . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
        }
        return $includePath . DIRECTORY_SEPARATOR . $this->vendor . DIRECTORY_SEPARATOR;
    }
    
    /**
     * method
     * 
     * @return ?ISemVer
     */
    
    protected function loadVersion()
    {
        if (defined(static::ION_PACKAGE_IGNORE_VERSION) && constant(static::ION_PACKAGE_IGNORE_VERSION) === true) {
            return null;
        }
        $path = $this->getProjectRoot() . static::ION_PACKAGE_VERSION_FILENAME;
        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data !== false) {
                $version = SemVer::parsePackageJson($data);
                if ($version !== null) {
                    return $version;
                }
            }
        }
        $path = $this->getProjectRoot() . static::COMPOSER_FILENAME;
        if (file_exists($path)) {
            $data = file_get_contents($path);
            if ($data !== false) {
                return SemVer::parseComposerJson($data);
            }
        }
        return null;
    }
    
    /**
     * method
     * 
     * @return ?ISemVer
     */
    
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getVendor() : string
    {
        return $this->vendor;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getProject() : string
    {
        return $this->project;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getName() : string
    {
        return $this->name;
    }
    
    /**
     * method
     * 
     * @return string
     */
    
    public function getProjectRoot() : string
    {
        return $this->projectRoot;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public function isCacheEnabled() : bool
    {
        return $this->enableCache;
    }
    
    /**
     * method
     * 
     * @return bool
     */
    
    public function isDebugEnabled() : bool
    {
        return $this->enableDebug;
    }
    
    /**
     * method
     * 
     * @return void
     */
    
    public function flushCache()
    {
        foreach ($this->loaders as $loader) {
            $loader->saveCache();
        }
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getCache() : array
    {
        return $this->cache;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getDevelopmentPaths() : array
    {
        return $this->sourcePaths;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getAdditionalPaths() : array
    {
        return $this->includePaths;
    }
    
    /**
     * method
     * 
     * @return array
     */
    
    public function getSearchPaths() : array
    {
        return $this->searchPaths;
    }

}