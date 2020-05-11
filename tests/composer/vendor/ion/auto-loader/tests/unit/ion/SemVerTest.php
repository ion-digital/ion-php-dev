<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion;

/**
 * Description of SemVerTest
 *
 * @author Justus
 */

use PHPUnit\Framework\TestCase;
use \ion\SemVer;

class SemVerTest extends TestCase {
        
    const MAJOR_VERSION = 1;
    const MINOR_VERSION = 2;
    const PATCH_VERSION = 3;
    const PRE_RELEASE = 'alpha';
    const META_1 = 'a';
    const META_2 = 'b';
    const META_3 = 'c';
    
    const VERSION_1 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '-' . self::PRE_RELEASE . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3;
    const VCS_VERSION_1 = 'v' . self::VERSION_1;
	
    const VERSION_2 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '-' . self::PRE_RELEASE;
    const VCS_VERSION_2 = 'v' . self::VERSION_2;

    const VERSION_3 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3;
    const VCS_VERSION_3 = 'v' . self::VERSION_3;

    const VERSION_4 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION;
    const VCS_VERSION_4 = 'v' . self::VERSION_4;

    const VERSION_5 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION;
    const VCS_VERSION_5 = 'v' . self::VERSION_5;	

    const VERSION_6 = self::MAJOR_VERSION;
    const VCS_VERSION_6 = 'v' . self::VERSION_6;	
    
    const VERSION_NONSENSE = 'asbjewhgrj#@$@#$.wr32.f';
	
    
    private function createInstance($preRelease = true, $metaData = true) {
        
        if($preRelease && $metaData) {        
            return SemVer::create(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION, static::PRE_RELEASE, [static::META_1, static::META_2, static::META_3]);
        }
        
        if($preRelease) {
            return SemVer::create(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION, static::PRE_RELEASE, []);
        }
        
        if($metaData) {
            return SemVer::create(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION, null, [static::META_1, static::META_2, static::META_3]);
        }
        
        return SemVer::create(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION);
    }
    
    public function testMajor() {
        $semVer = static::createInstance();
        
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
    }

    
    public function testGetMinor() {
        $semVer = static::createInstance();
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
    }

    
    public function testGetPatch() {
        $semVer = static::createInstance();
        
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
    }

    public function testGetPreRelease() {
        $semVer = static::createInstance(true);
        
        $this->assertEquals('alpha', $semVer->getPreRelease());
    }

    public function testGetMetaData() {
        $semVer = static::createInstance(true, true);
        
        $this->assertEquals(3, count($semVer->getMetaData()));
        
        $this->assertEquals(static::META_1, $semVer->getMetaData()[0]);
        $this->assertEquals(static::META_2, $semVer->getMetaData()[1]);
        $this->assertEquals(static::META_3, $semVer->getMetaData()[2]);
    }
    
    
    
    public function testToString() {
        $semVer = static::createInstance(true, true);        
        $this->assertEquals(static::MAJOR_VERSION . '.' . static::MINOR_VERSION . '.' . static::PATCH_VERSION . '-' . self::PRE_RELEASE . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3, $semVer->toString());
        
        $semVer = static::createInstance(true, false);        
        $this->assertEquals(static::MAJOR_VERSION . '.' . static::MINOR_VERSION . '.' . static::PATCH_VERSION . '-' . self::PRE_RELEASE, $semVer->toString());

        $semVer = static::createInstance(false, true);        
        $this->assertEquals(static::MAJOR_VERSION . '.' . static::MINOR_VERSION . '.' . static::PATCH_VERSION . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3, $semVer->toString());
        
        $semVer = static::createInstance(false, false);        
        $this->assertEquals(static::MAJOR_VERSION . '.' . static::MINOR_VERSION . '.' . static::PATCH_VERSION, $semVer->toString());        
    }

    public function testToVcsTag() {
        $semVer = static::createInstance();
        
        $this->assertEquals('v' . static::MAJOR_VERSION . '.' . static::MINOR_VERSION . '.' . static::PATCH_VERSION . '-' . self::PRE_RELEASE . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3, $semVer->toVcsTag());       
    }

    
    public function testToArray() {
        $semVer = static::createInstance();
        
        $this->assertEquals([static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION, static::PRE_RELEASE, [static::META_1, static::META_2, static::META_3]], $semVer->toArray());        
    }
    
    public function testIsHigherThan() {
        
        $semVer1 = new SemVer(0, 0, 10);                              

        $this->assertEquals(true, $semVer1->isHigherThan(new SemVer(0,0,1)));
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(0,0,10)));          
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(0,1,0)));
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(1,0,0)));
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(10,0,0)));
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(10,10,0)));
        $this->assertEquals(false, $semVer1->isHigherThan(new SemVer(10,10,10)));
        
        $semVer2 = new SemVer(0, 10, 10);                              

        $this->assertEquals(true, $semVer2->isHigherThan(new SemVer(0,0,1)));
        $this->assertEquals(true, $semVer2->isHigherThan(new SemVer(0,0,10)));          
        $this->assertEquals(true, $semVer2->isHigherThan(new SemVer(0,1,0)));
        $this->assertEquals(false, $semVer2->isHigherThan(new SemVer(1,0,0)));        
        $this->assertEquals(false, $semVer2->isHigherThan(new SemVer(10,0,0)));
        $this->assertEquals(false, $semVer2->isHigherThan(new SemVer(10,10,0)));
        $this->assertEquals(false, $semVer2->isHigherThan(new SemVer(10,10,10)));        
        
        $semVer3 = new SemVer(10, 10, 10);                              

        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(0,0,1)));
        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(0,0,10)));          
        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(0,1,0)));
        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(1,0,0)));                 
        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(10,0,0)));
        $this->assertEquals(true, $semVer3->isHigherThan(new SemVer(10,10,0)));
        $this->assertEquals(false, $semVer3->isHigherThan(new SemVer(10,10,10)));
        
    }
 
    
    public function testIsLowerThan() {
        $semVer1 = new SemVer(0, 0, 10);                              

        $this->assertEquals(false, $semVer1->isLowerThan(new SemVer(0,0,1)));
        $this->assertEquals(false, $semVer1->isLowerThan(new SemVer(0,0,10)));          
        $this->assertEquals(true, $semVer1->isLowerThan(new SemVer(0,1,0)));
        $this->assertEquals(true, $semVer1->isLowerThan(new SemVer(1,0,0)));
        $this->assertEquals(true, $semVer1->isLowerThan(new SemVer(10,0,0)));
        $this->assertEquals(true, $semVer1->isLowerThan(new SemVer(10,10,0)));
        $this->assertEquals(true, $semVer1->isLowerThan(new SemVer(10,10,10)));           
        
        $semVer2 = new SemVer(0, 10, 10);                              

        $this->assertEquals(false, $semVer2->isLowerThan(new SemVer(0,0,1)));
        $this->assertEquals(false, $semVer2->isLowerThan(new SemVer(0,0,10)));          
        $this->assertEquals(false, $semVer2->isLowerThan(new SemVer(0,1,0)));
        $this->assertEquals(true, $semVer2->isLowerThan(new SemVer(1,0,0)));        
        $this->assertEquals(true, $semVer2->isLowerThan(new SemVer(10,0,0)));
        $this->assertEquals(true, $semVer2->isLowerThan(new SemVer(10,10,0)));
        $this->assertEquals(true, $semVer2->isLowerThan(new SemVer(10,10,10)));           
        
        $semVer3 = new SemVer(10, 10, 10);                              

        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(0,0,1)));
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(0,0,10)));          
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(0,1,0)));
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(1,0,0)));    
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(10,0,0)));
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(10,10,0)));
        $this->assertEquals(false, $semVer3->isLowerThan(new SemVer(10,10,10)));           
    }
        

    public function testIsEqualTo()  {
        $semVer = new SemVer(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION);
        $this->assertEquals(true, $semVer->isEqualTo(new SemVer(static::MAJOR_VERSION, static::MINOR_VERSION, static::PATCH_VERSION)));
        
        //TODO: These could probably be more dynamic
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,0,0)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,1,0)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,2,0)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,2,1)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,2,2)));        
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,2,4)));        
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(1,3,0)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(2,0,0)));
        $this->assertEquals(false, $semVer->isEqualTo(new SemVer(2,2,0))); 
        
    }  
    
    public function testParse() {
        
		// VERSION_1 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '-' . self::PRE_RELEASE . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3;

        $semVer = SemVer::parse(static::VERSION_1);    
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(static::PRE_RELEASE, $semVer->getPreRelease());
        $this->assertEquals(static::META_1, $semVer->getMetaData()[0]);
        $this->assertEquals(static::META_2, $semVer->getMetaData()[1]);
        $this->assertEquals(static::META_3, $semVer->getMetaData()[2]);
        
        
        $semVer = SemVer::parse(static::VCS_VERSION_1);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(static::PRE_RELEASE, $semVer->getPreRelease());
        $this->assertEquals(static::META_1, $semVer->getMetaData()[0]);
        $this->assertEquals(static::META_2, $semVer->getMetaData()[1]);
        $this->assertEquals(static::META_3, $semVer->getMetaData()[2]);

		// VERSION_2 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '-' . self::PRE_RELEASE;

        $semVer = SemVer::parse(static::VERSION_2);    
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(static::PRE_RELEASE, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));
        
        
        $semVer = SemVer::parse(static::VCS_VERSION_2);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(static::PRE_RELEASE, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));	
		
		// VERSION_3 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . '+' . self::META_1 . '.' . self::META_2 . '.' . self::META_3;		
		
        $semVer = SemVer::parse(static::VERSION_3);    
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(static::META_1, $semVer->getMetaData()[0]);
        $this->assertEquals(static::META_2, $semVer->getMetaData()[1]);
        $this->assertEquals(static::META_3, $semVer->getMetaData()[2]);
                
        $semVer = SemVer::parse(static::VCS_VERSION_3);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(static::META_1, $semVer->getMetaData()[0]);
        $this->assertEquals(static::META_2, $semVer->getMetaData()[1]);
        $this->assertEquals(static::META_3, $semVer->getMetaData()[2]);		
	
		// VERSION_4 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION		
	
        $semVer = SemVer::parse(static::VERSION_4);    
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));
                
        $semVer = SemVer::parse(static::VCS_VERSION_4);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(static::PATCH_VERSION, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));
	
		// VERSION_5 = self::MAJOR_VERSION . '.' . self::MINOR_VERSION;
	
        $semVer = SemVer::parse(static::VERSION_5);    
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(0, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));
        
        
        $semVer = SemVer::parse(static::VCS_VERSION_5);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(static::MINOR_VERSION, $semVer->getMinor());
        $this->assertEquals(0, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));	
		
		// VERSION_6 = self::MAJOR_VERSION;				
		
        $semVer = SemVer::parse(static::VERSION_6);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(0, $semVer->getMinor());
        $this->assertEquals(0, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));			
		
        $semVer = SemVer::parse(static::VCS_VERSION_6);   
        $this->assertEquals(true, $semVer !== null);
        $this->assertEquals(static::MAJOR_VERSION, $semVer->getMajor());
        $this->assertEquals(0, $semVer->getMinor());
        $this->assertEquals(0, $semVer->getPatch());
        $this->assertEquals(null, $semVer->getPreRelease());
        $this->assertEquals(0, count($semVer->getMetaData()));			
		
        $semVer = SemVer::parse(static::VERSION_NONSENSE);   
        $this->assertEquals(true, $semVer === null);
       
        
		
        /*
1.0.0
1.0.2
1.1.0
0.2.5
1.0.0-dev
1.0.0-alpha3
1.0.0-beta2
1.0.0-RC5
v2.0.4-p1
         */        
        
    }
    
}
