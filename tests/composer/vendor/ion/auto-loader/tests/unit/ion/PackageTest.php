<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion;

/**
 * Description of PackageTest
 *
 * @author Justus
 */

use \ion\Package;
use \ion\ISemVer;
use \ion\SemVer;
use \ion\Packages\PackageException;
use PHPUnit\Framework\TestCase;
use \ion\Packages\AutoLoader;

class PackageTest extends TestCase {
    
    const TEST_PACKAGE_VENDOR = 'xyz';
    const TEST_PACKAGE_PROJECT = 'package';
    const TEST_PACKAGE = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT;
    
    const TEST_PACKAGE_PROJECT_1 = self::TEST_PACKAGE_PROJECT . '_1';
    const TEST_PACKAGE_PROJECT_2 = self::TEST_PACKAGE_PROJECT . '_2';    
    const TEST_PACKAGE_PROJECT_3 = self::TEST_PACKAGE_PROJECT . '_3'; 
    const TEST_PACKAGE_PROJECT_4 = self::TEST_PACKAGE_PROJECT . '_4'; 
    const TEST_PACKAGE_PROJECT_5 = self::TEST_PACKAGE_PROJECT . '_5'; 
    const TEST_PACKAGE_PROJECT_6 = self::TEST_PACKAGE_PROJECT . '_6'; 
    const TEST_PACKAGE_PROJECT_7 = self::TEST_PACKAGE_PROJECT . '_7'; 
    const TEST_PACKAGE_PROJECT_8 = self::TEST_PACKAGE_PROJECT . '_8'; 
    const TEST_PACKAGE_PROJECT_9 = self::TEST_PACKAGE_PROJECT . '_9';  
    const TEST_PACKAGE_PROJECT_10 = self::TEST_PACKAGE_PROJECT . '_10'; 
    
    const TEST_PACKAGE_1 = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT_1;
    const TEST_PACKAGE_2 = self::TEST_PACKAGE_VENDOR . '/' . self::TEST_PACKAGE_PROJECT_2;
    
    const AUTO_LOADER_PROJECT_DIR = '../../data/';
    
    const SOURCE_DIRECTORY = './a/';
    const EXTRA_DIRECTORY_1 = './b/';
    const EXTRA_DIRECTORY_2 = './c/';
    const NON_EXISTENT_DIRECTORY = './non_existent/';
    
    const MAJOR_VERSION = 1;
    const MINOR_VERSION = 2;
    const PATCH_VERSION = 3;
    
    
    private static function createPackage(string $project, bool $debug = true, bool $cache = false, array $loaders = null, bool $createVersion = true) {
        
        $version = null;
        
       // echo "CREATING PROJECT: $project\n";
        
        if($createVersion === true) {
            $version = new SemVer(self::MAJOR_VERSION, self::MINOR_VERSION, self::PATCH_VERSION);
        }
        
//        echo "INSTANCES: ";
//        foreach(Package::getInstances() as $instance) {
//            echo $instance->getProject() . "  ";
//        }
//        echo "\n";
        
        return Package::create(self::TEST_PACKAGE_VENDOR, $project, [ self::SOURCE_DIRECTORY ], [
            self::EXTRA_DIRECTORY_1,
            self::EXTRA_DIRECTORY_2,
            self::NON_EXISTENT_DIRECTORY
        ], realpath(__DIR__ . DIRECTORY_SEPARATOR . self::AUTO_LOADER_PROJECT_DIR), $version, $debug, $cache, $loaders);     
        
    }
    
    
    
    
    public function testCreate() {
        
        $package1 = self::createPackage(self::TEST_PACKAGE_PROJECT_1, true, false);
        $package2 = self::createPackage(self::TEST_PACKAGE_PROJECT_2, false, false);        
        
        $this->assertEquals(2, count(Package::getInstances()));
        
        $this->assertEquals(self::TEST_PACKAGE_1, Package::getInstance(self::TEST_PACKAGE_1)->getName());
        $this->assertEquals(self::TEST_PACKAGE_2, Package::getInstance(self::TEST_PACKAGE_2)->getName());
        
        $this->assertEquals(self::TEST_PACKAGE_1, Package::getInstances()[self::TEST_PACKAGE_1]->getName());
        $this->assertEquals(self::TEST_PACKAGE_2, Package::getInstances()[self::TEST_PACKAGE_2]->getName());        
        
        $this->assertEquals(true, $package1->isDebugEnabled());
        $this->assertEquals(false, $package1->isCacheEnabled());
        $this->assertEquals(3, count($package1->getAdditionalPaths()));
        $this->assertEquals(1, count($package1->getSearchPaths()));
        
        $this->assertEquals(false, $package2->isDebugEnabled());
        $this->assertEquals(false, $package2->isCacheEnabled());
        $this->assertEquals(3, count($package2->getAdditionalPaths()));
        $this->assertEquals(3, count($package2->getSearchPaths()));        
        
        $package1->destroy();
        $package2->destroy();
        
    }
    
    public function testProperties() {
        
        $package = self::createPackage(self::TEST_PACKAGE_PROJECT, true, false);
                        
        $this->assertEquals(1, $package->getVersion()->getMajor());
        $this->assertEquals(2, $package->getVersion()->getMinor());
        $this->assertEquals(3, $package->getVersion()->getPatch());
        
        $this->assertEquals(self::TEST_PACKAGE_VENDOR, $package->getVendor());
        $this->assertEquals(self::TEST_PACKAGE_PROJECT, $package->getProject());
        $this->assertEquals(self::TEST_PACKAGE, $package->getName());

        //$this->assertEquals(realpath(__DIR__ . '../../../'), realpath($package->getProjectRoot()));
        
        $package->destroy();
    }
    
    public function testAdapters() {
        $this->expectException(PackageException::class);        
        self::createPackage(self::TEST_PACKAGE_PROJECT_4, true, false, [ 'a_non_existent_class' ]);         
    }
    
    public function testLoad() {
        
        $package = self::createPackage(self::TEST_PACKAGE_PROJECT_5, false, false);        
        
        $this->assertEquals(false, $package->isDebugEnabled());
        $this->assertEquals(false, $package->isCacheEnabled());
        
        $this->assertEquals(false, class_exists('\\Tests\\TestClass1', false));
        $testClass1 = new \Tests\TestClass1();
        $this->assertEquals(true, class_exists('\\Tests\\TestClass1', false));        
        
        $this->assertEquals(false, class_exists('\\TestClass3', false));
        $testClass3 = new \TestClass3();
        $this->assertEquals(true, class_exists('\\TestClass3', false));     
        
        $package->destroy();
    }
    
    public function testCache() {

        $package = self::createPackage(self::TEST_PACKAGE_PROJECT_6, false, true);        

        $this->assertEquals(false, $package->isDebugEnabled());
        $this->assertEquals(true, $package->isCacheEnabled());
        
        $this->assertEquals(false, class_exists('\\Tests\\TestClass2', false));
        $testClass2 = new \Tests\TestClass2();
        $this->assertEquals(true, class_exists('\\Tests\\TestClass2', false));        
        
        $this->assertEquals(false, class_exists('\\TestClass4', false));
        $testClass4 = new \TestClass4();
        $this->assertEquals(true, class_exists('\\TestClass4', false));
                
        $a = Package::createSearchPath($package, self::SOURCE_DIRECTORY);
        $b = Package::createSearchPath($package, self::EXTRA_DIRECTORY_1);
        $c = Package::createSearchPath($package, self::EXTRA_DIRECTORY_2);

        $this->assertEquals(true, $a !== null);
        $this->assertEquals(true, $b !== null);
        $this->assertEquals(true, $c !== null);
        
        $this->assertEquals(true, in_array($a, $package->getSearchPaths(false)));
        $this->assertEquals(true, in_array($b, $package->getSearchPaths(false)));
        $this->assertEquals(true, in_array($c, $package->getSearchPaths(false)));

        $aId = AutoLoader::createDeploymentId($package, $a);
        $bId = AutoLoader::createDeploymentId($package, $b);
        $cId = AutoLoader::createDeploymentId($package, $c);                      
        
        $a = $a . AutoLoader::createCacheFilename($aId);
        $b = $b . AutoLoader::createCacheFilename($bId);
        $c = $c . AutoLoader::createCacheFilename($cId);        
             
        //die("\n\n$a\n\n");
        
        $package->flushCache();
        
        $this->assertEquals(true, file_exists($a));
        $this->assertEquals(false, file_exists($b));
        $this->assertEquals(true, file_exists($c));
        
        $package->destroy();
    }
    
    public function testDebug() {
		        
        $package1 = self::createPackage(self::TEST_PACKAGE_PROJECT_7, true);     
        
        $this->assertEquals(true, $package1->isDebugEnabled());
        $this->assertEquals(false, $package1->isCacheEnabled());        
        $this->assertEquals(3, count($package1->getAdditionalPaths()));
        $this->assertEquals(1, count($package1->getSearchPaths()));        
        
        $package2 = self::createPackage(self::TEST_PACKAGE_PROJECT_8, false);      
        
        $this->assertEquals(false, $package2->isDebugEnabled());     
        $this->assertEquals(false, $package2->isCacheEnabled());
        $this->assertEquals(3, count($package2->getAdditionalPaths()));
        $this->assertEquals(3, count($package2->getSearchPaths()));
        
        $package1->destroy();
        $package2->destroy();
    }
    
    public function testLoadVersion() {
        
        $package =  self::createPackage(self::TEST_PACKAGE_PROJECT_9, true, false, null, false);                          
                                
        $this->assertEquals(true, file_exists($package->getProjectRoot() . 'root.txt'));
        
        $this->assertEquals(true, file_exists($package->getProjectRoot() . 'version.json'));
        
        $this->assertEquals(9, $package->getVersion()->getMajor());
        $this->assertEquals(9, $package->getVersion()->getMinor());
        $this->assertEquals(9, $package->getVersion()->getPatch());
        
        $this->assertEquals('tests', $package->getVersion()->getPreRelease());
        
        $this->assertEquals(3, count($package->getVersion()->getMetaData()));
        
        $package->destroy();
    }
    
//    public function testValidation() {
//        
//        $package =  self::createPackage(self::TEST_PACKAGE_PROJECT_10, false, true);                          
//                              
//        $this->assertEquals(false, class_exists('\\Tests\\TestClass7', false));
//        $testClass7 = new \Tests\TestClass7();
//        $this->assertEquals(true, class_exists('\\Tests\\TestClass7', false));
//        
//        $this->assertEquals(1, count($package->getSearchPaths(true)));
//        
//        $this->assertEquals(false, class_exists('\\TestClass8', false));
//        $testClass8 = new \TestClass8();
//        $this->assertEquals(true, class_exists('\\TestClass8', false));        
//        
//        $this->assertEquals(2, count($package->getSearchPaths(true)));
//        
//        $package->destroy();
//    }
    
}
