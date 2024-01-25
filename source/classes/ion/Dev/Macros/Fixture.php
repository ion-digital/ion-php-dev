<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Macros;

/**
 * Description of MacroFixture
 *
 * @author Justus.Meyer
 */

use \DOMDocument;
use \Exception;
use \DOMException;
use \stdClass;

class Fixture {
        
    const PREFIX_PATTERN = '\/\s*\*\s*\{\s*';
    const SUFFIX_PATTERN = '\s*\}\s*\*\s*\/';
    
    private static $defaultPrefix = self::PREFIX_PATTERN;
    private static $defaultSuffix = self::SUFFIX_PATTERN;
    private static $fixtures = [];
    
    public static function getDefaultPrefixPattern(): string {
    
        return static::$defaultPrefix;
    }
    
    public static function getDefaultSuffixPattern(): string {
    
        return static::$defaultSuffix;
    }    
    
    public static function &getFixtures(): array {
        
        return static::$fixtures;
    }
    
    private static $baseDir = null;
    private static $inputDir = null;

    public static function create(
            string $name, 
            array $tags, 
            string $method = null, 
            string $inherits = null,             
            string $base = null, 
            string $output = null, 
            string $working = null,
            bool $suppressOutput = false,
            array $macros = [], 
            array $defaults = [], 
            string $prefixPattern = null, 
            string $suffixPattern = null): self {
        
        return new static($name, $tags, $method, $inherits, $base, $output, $working, $suppressOutput, $macros, $defaults, $prefixPattern, $suffixPattern);
    }
    
    public static function load(string $path, callable $onSuccess, callable $onFailure = null, bool $suppressOutput = false): void {

        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            
            if ($errno == E_WARNING && (substr_count($errstr, 'DOMDocument::loadXML()') > 0))
            {

                throw new DOMException($errstr);
            }

            return false;
        });
        
        $xml = new DOMDocument();
        
        $cwd = getcwd() . DIRECTORY_SEPARATOR;
        
        $nwd = dirname(realpath($path)) . DIRECTORY_SEPARATOR;
        
        $output = null;
        
        $prefixPattern = null;
        $suffixPattern = null;
        $defaults = [];
        
        if(!realpath($nwd) || !file_exists($nwd)) {

            throw new Exception("No fixture could be found for import in '{$path}.'.'");
        }               

        chdir($nwd);
        
        try {

            $xml->load($path, LIBXML_BIGLINES | LIBXML_COMPACT /* | LIBXML_NOEMPTYTAG | LIBXML_NOERROR | LIBXML_NOWARNING */ | LIBXML_NONET | LIBXML_PARSEHUGE );
            
        } catch(Exception $ex) {
            
            $onFailure($path, $ex->getMessage());
            
        } finally {
            restore_error_handler();
            
            
        }
                
        if(static::$inputDir === null) {
            
            static::$inputDir = realpath('.') . DIRECTORY_SEPARATOR;
        }        
        
        $fixturesNodes = $xml->getElementsByTagName('fixtures'); 
        
        foreach($fixturesNodes as $fixturesNode) {

            $fixturesAttr = [];

            foreach ($fixturesNode->attributes as $domAttr) {

                $fixturesAttr[$domAttr->localName] = $domAttr->nodeValue;
            }                        
            
            if(static::$baseDir === null) {
            
                if(array_key_exists('base', $fixturesAttr)) {

                    static::$baseDir = $fixturesAttr['base'];

                } else {

                    static::$baseDir = realpath('.') . DIRECTORY_SEPARATOR;
                }
            }            
            
            if(!realpath(static::$baseDir)) {
                
                if(!realpath($cwd . DIRECTORY_SEPARATOR . static::$baseDir)) {
                    
                    throw new Exception("Could not find base directory '" . $cwd . DIRECTORY_SEPARATOR . static::$baseDir . " (specified in '{$path}').'");
                }
                
                static::$baseDir = $cwd . DIRECTORY_SEPARATOR . static::$baseDir;
            }
            
            static::$baseDir = rtrim(realpath(static::$baseDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            
            // Handle the imports
            
            $importsNodes = $fixturesNode->getElementsByTagName('imports'); 
            
            foreach($importsNodes as $importsNode) {
            
                $importNodes = $importsNode->getElementsByTagName('import'); 

                foreach($importNodes as $importNode) {
                 
                    $tagAttr = [];
                    
                    if(!$importNode->hasAttributes()) {
                        
                        throw new Exception("No attributes have been specified for import in '{$path}.'");
                    }
                    
                    foreach ($importNode->attributes as $domAttr) {
                        
                        $tagAttr[$domAttr->localName] = $domAttr->nodeValue;
                    }  
                    
                    if(!array_key_exists('path', $tagAttr)) {
                        
                        throw new Exception("Path not specified for import in '{$path}.'");
                    }                    

                    
                    $tmpPath = realpath(getcwd() . DIRECTORY_SEPARATOR . $tagAttr['path']); 
                    
                    if(empty($tmpPath)) {
                        
                        throw new Exception("Import file could not be found ('{$path}').");
                    }
                    
                    static::load($tmpPath, $onSuccess, $onFailure, (array_key_exists('use', $tagAttr) ? true : false));
                                       
                }
            }
            
            // Handle the defaults
            
            $defaultsNodes = $fixturesNode->getElementsByTagName('defaults');
            
            foreach($defaultsNodes as $defaultsNode) {
                
                $defaultNodes = $defaultsNode->getElementsByTagName('default');
            
                foreach($defaultNodes as $defaultNode) {                
                
                        $defaultAttr = [];                        

                        if(!$defaultNode->hasAttributes()) {

                            throw new Exception("No attributes have been specified for default in '{$path}.'");
                        }

                        foreach ($defaultNode->attributes as $domAttr) {

                            $defaultAttr[$domAttr->localName] = $domAttr->nodeValue;
                        }  

                        if(!array_key_exists('name', $defaultAttr)) {

                            throw new Exception("Name was not specified for default in '{$path}.'");
                        }                           

                        $defaults[$defaultAttr['name']] = [
                            'content' => [],
                            'method' => null,
                            'filters' => []
                        ];
                        
                        $defaults[$defaultAttr['name']]['content'][] = $defaultNode->nodeValue;
                        
                        if(array_key_exists('method', $defaultAttr)) {
                            $m = strtolower($defaultAttr['method']);

                            if($m === 'prepend' || $m === 'append' || $m === 'parent' || $m === 'child') {

                                $defaults[$defaultAttr['name']]['method'] = $m;
                            }
                        }
                        
                        if(array_key_exists('filters', $defaultAttr)) {
                            
                            $defaults[$defaultAttr['name']]['filters'] = explode(' ', strtolower($defaultAttr['filters']));
                        }
                    
                
                }
            }            

            // Handle the fixtures
            
            $fixtureNodes = $fixturesNode->getElementsByTagName('fixture');
            
            foreach($fixtureNodes as $fixtureNode) {
                
                // name
                
                $fixtureAttr = [];                        

                foreach ($fixtureNode->attributes as $domAttr) {

                    $fixtureAttr[$domAttr->localName] = $domAttr->nodeValue;
                }     
                
                $fixtureName = null;                
                
                if(!array_key_exists('name', $fixtureAttr)) {

                    if(array_key_exists('', static::getFixtures())) {
                        
                        throw new Exception("Name was not specified for multiple fixtures in '{$path}' - only one instance of this is allowed (to specify defaults).");
                    }
                    
                    $fixtureName = '';
                    
                } else {
                    
                    $fixtureName = $fixtureAttr['name'];
                }
                
                // inherits
                
                $inheritsName = null;
                
                if(array_key_exists('inherits', $fixtureAttr)) {
                                    
                    $inheritsName = $fixtureAttr['inherits'];
                }
                
                // output
                
                $output = null;
                
                if(array_key_exists('output', $fixtureAttr)) {
                    
                    $output = str_replace($cwd . DIRECTORY_SEPARATOR, '', getcwd());
                    
                    if(!empty($output)) {
                        
                        $output .= DIRECTORY_SEPARATOR;
                    }
                    
                    $output .= $fixtureAttr['output'];
                    
                    //echo "[$output]\n";
                }
                
                // method
                
                $method = null;
                
                if(array_key_exists('method', $fixtureAttr)) {
                                    
                    $method = $fixtureAttr['method'];
                }                
                
                // macro
                
                $macros = [];
                $filters = [];
                
                $macroNodes = $fixtureNode->getElementsByTagName('template');
                
                foreach($macroNodes as $macroNode) {
                    
                    $macroAttr = []; 
                    $macroMethod = 'append';
                    
                    $content = $macroNode->textContent;
                    
                    foreach ($macroNode->attributes as $domAttr) {

                        $macroAttr[$domAttr->localName] = $domAttr->nodeValue;
                    }                       

                    $macro = [
                        'content' => [],
                        'filters' => []
                    ];
                    
                    if(array_key_exists('method', $macroAttr)) {
                        
                        $macroMethod = $macroAttr['method'];
                    }                    
                    
                    if(array_key_exists('path', $macroAttr)) {
                        
                        $tmpPath = realpath($macroAttr['path']);
                        
                        if(!file_exists($tmpPath)) {
                            
                            throw new Exception("Could not find macro '{$tmpPath}' in macro '{$path}.'");
                        }
                        
                        $f = file_get_contents($tmpPath);
                        
                        switch($macroMethod) {
                            
                            case 'prepend': {
                                
                                $macro['content'][] = $f;
                                $macro['content'][] = $content;
                                break;
                            }
                            
                            case 'replace': {
                               
                                $macro['content'] = [ $f ];
                                break;
                            }     
                            
                            case 'append':                                     
                            default: {
                             
                                $macro['content'][] = $content;
                                $macro['content'][] = $f;
                                break;
                            }                          
                        }
                    } else {
                        
                        $macro['content'] = [ $content ];
                    }

                    if(array_key_exists('filters', $macroAttr)) {
                        
                        $filters = explode(' ', strtolower($macroAttr['filters']));
                        
                        foreach($filters as &$filter) {
                            
                            $filter = trim($filter);
                        }
                        
                        $macro['filters'] = $filters;
                    }                           
                    
                    $macros[] = $macro;
                    
                    break; // break to stop the loop and only process the first entry                    
                }
                
                // tags
                
                $tags = [];                
                
                $tagsNodes = $fixtureNode->getElementsByTagName('tags');
                
                foreach($tagsNodes as $tagsNode) {
                    
                    $tagNodes = $fixtureNode->getElementsByTagName('tag');

                    foreach($tagNodes as $tagNode) {

                        
                        $tagAttr = [];                        

                        if(!$tagNode->hasAttributes()) {

                            throw new Exception("No attributes have been specified for tag in '{$path}.'");
                        }

                        foreach ($tagNode->attributes as $domAttr) {

                            $tagAttr[$domAttr->localName] = $domAttr->nodeValue;
                        }  

                        if(!array_key_exists('name', $tagAttr)) {

                            throw new Exception("Name was not specified for tag in '{$path}.'");
                        }                           

                        $tags[$tagAttr['name']] = [
                            'content' => [],
                            'method' => null,
                            'filters' => []
                        ];
                        
                        $tags[$tagAttr['name']]['content'][] = $tagNode->nodeValue;
                        
                        if(array_key_exists('method', $tagAttr)) {
                            $m = strtolower($tagAttr['method']);

                            if($m === 'prepend' || $m === 'append' || $m === 'parent' || $m === 'child') {

                                $tags[$tagAttr['name']]['method'] = $m;
                            }
                        }
                        
                        if(array_key_exists('filters', $tagAttr)) {
                            
                            $tags[$tagAttr['name']]['filters'] = explode(' ', strtolower($tagAttr['filters']));
                        }

                    }                    
                }

                static::getFixtures()[$fixtureName] = new Fixture(
                        $fixtureName, 
                        $tags, 
                        $method, 
                        $inheritsName, 
                        $cwd, 
                        $output, 
                        $nwd,
                        $suppressOutput,
                        $macros, 
                        $defaults, 
                        $prefixPattern, 
                        $suffixPattern);
            }
        }
        
        chdir($cwd);

        
        $onSuccess($path);
        
        return;
    }
    
    public static function applyTags(string $content, array $tags, array $defaults, string $prefixPattern, string $suffixPattern, self $fixture = null): string {
                
//        foreach($tags as $tagName => $tag) {
//            
//            $tagContent = '';
//            
//            foreach($tag['content'] as $t) {
//                
//                $tagContent .= $t;
//            }
//            
//            //$tagContent = static::applyFilters($tag['content'], $tag['filters']);
//            $tagContent = static::applyFilters($tagContent, $tag['filters']);
//            
//            $pattern = "({$prefixPattern})({$tagName})({$suffixPattern})";
//            
//            if(in_array('clear', $tag['filters']) || in_array('clear-left', $tag['filters'])) {
//                
//                $pattern = "\\s*{$pattern}";
//            }
//
//            if(in_array('clear', $tag['filters']) || in_array('clear-right', $tag['filters'])) {
//                
//                $pattern = "{$pattern}\\s*";
//            }            
//            
//            $pattern = "/{$pattern}/";
//            
//            while(preg_match($pattern, $content) === 1) {
//
//                $content = preg_replace($pattern, $tagContent, $content);
//            }            
//        }        
        
        $tags = array_merge($defaults, $tags);
        

        
        // (\/\*)[^{]+{([^{}])+[^*]+(\*\/)
        $matchPattern = "{$prefixPattern}([^}]+){$suffixPattern}";
        
        $matches = [];
        
        while(preg_match("/{$matchPattern}/", $content, $matches) === 1) {
                        
            if(count($matches) > 1) {

                $tagName = $matches[1];
                $tagContent = '';
                $replacePattern = "{$prefixPattern}{$tagName}{$suffixPattern}";

                if(array_key_exists($tagName, $tags)) {                   

                    $tag = $tags[$tagName];

                    foreach($tag['content'] as $t) {

                        $tagContent .= $t;
                    }

                    //$tagContent = static::applyFilters($tag['content'], $tag['filters']);
                    $tagContent = static::applyFilters($tagContent, $tag['filters']);

                    if(in_array('clear', $tag['filters']) || in_array('clear-left', $tag['filters'])) {

                        $replacePattern = "\\s*{$replacePattern}";
                    }

                    if(in_array('clear', $tag['filters']) || in_array('clear-right', $tag['filters'])) {

                        $replacePattern = "{$replacePattern}\\s*";
                    }            

                    while(preg_match("/{$replacePattern}/", $content) === 1) {

                        $content = preg_replace("/{$replacePattern}/", $tagContent, $content);
                    }                

                    continue;

                }

                $content = preg_replace("/{$replacePattern}/", '', $content);
                
                continue;
            }            
            
            break;
        }
//        
//            if($fixture !== null && $fixture->getName() === 'ObjectMap') {
//                var_Dump($content);
//                
//                die('xXx');
//            }               

        return $content;
    }
    
    public static function applyFilters(string $content, array $filters): string {

        if(in_array('eval', $filters)) {

            ob_start();
            eval("?>$content");
            $content = ob_get_clean();      
        }                    

        $t = $content;
        
        //var_dump($filters);
        
        if(in_array('trim-lines-left', $filters) || in_array('trim-lines', $filters)) {

            $ls = explode("\n", $t);
            $tmp = '';
            
            foreach($ls as $l) {
                
                $tmp .= ltrim($l) . "\n";
            }       
            
            $t = $tmp;
        }            

        if(in_array('trim-lines-right', $filters) || in_array('trim-lines', $filters)) {

            $ls = explode("\n", $t);
            $tmp = '';
            
            foreach($ls as $l) {
                
                $tmp .= rtrim($l) . "\n";
            }    
            
            
            $t = $tmp;
        }            

        if(in_array('trim-left', $filters) || in_array('trim', $filters)) {

            $t = ltrim($t);
        }    

        if(in_array('trim-right', $filters) || in_array('trim', $filters)) {

            $t = rtrim($t);
        }
        
        if(in_array('trim-paragraph-top', $filters) || in_array('trim-paragraph', $filters)) {

            $t = ltrim($t, "\n\r");
        }  
        
        if(in_array('trim-paragraph-bottom', $filters) || in_array('trim-paragraph', $filters)) {

            $t = rtrim($t, "\n\r");
        }              
        
        if(in_array('buffer-paragraph-top', $filters) || in_array('buffer-paragraph', $filters)) {

            $t = "\n" . $t;
        }  
        
        if(in_array('buffer-paragraph-bottom', $filters) || in_array('buffer-paragraph', $filters)) {

            $t .= "\n";
        }          
        
        return $t;
    }
    
    private $tags;
    private $method;
    private $tagFilters;
    private $name;
    private $inherits;
    private $macros;
    private $base;
    private $output;
    private $working;
    private $prefixPattern;
    private $suffixPattern;
    private $defaults;
    private $suppressOutput;
    
    protected function __construct(
            string $name, 
            array $tags, 
            string $method = null, 
            string $inherits = null,            
            string $base = null, 
            string $output = null,  
            string $working = null,
            bool $suppressOutput = false,
            array $macros = [], 
            array $defaults = [], 
            string $prefixPattern = null, 
            string $suffixPattern = null) {
     
        $this->name = $name;
        $this->tags = $tags;
        $this->method = ($method === null ? 'append' : $method);
        $this->base = $base;
        $this->output = $output;
        $this->working = $working;
        $this->suppressOutput = $suppressOutput;
        $this->macros = $macros;
        $this->defaults = $defaults;
        $this->prefixPattern = ($prefixPattern !== null ? $prefixPattern : static::getDefaultPrefixPattern());
        $this->suffixPattern = ($suffixPattern !== null ? $suffixPattern : static::getDefaultSuffixPattern());
        
        if($inherits !== null) {
            
            $this->inherits = $inherits;
        }
        
        if(!array_key_exists($name, static::getFixtures())) {
            
            static::getFixtures()[$name] = $this;
        }
    }
    
    public function getOutput(): ?string {
        
        return $this->output;
    }
    
    public function getWorking(): ?string {
        
        return $this->working;
    }
    
    public function getSuppressOutput(): bool {
        
        return $this->suppressOutput;
    }    
    
    public function getBase(): ?string {
        
        return $this->base;
    }
    
    public function getTags(): array {
        
        return $this->tags;
    }
    
    public function getName(): string {
        
        return $this->name;
    }
    
    public function getParent(): ?self {
        
        if(array_key_exists($this->inherits, static::getFixtures())) {
        
            return static::getFixtures()[$this->inherits];
        }
        
        return null;
    }
    
    public function getMacros(): ?array {
        
        return $this->macros;
    }    
    
    public function getDefaults(): ?array {
        
        return $this->defaults;
    }
    
    public function getMethod(): string {
        
        return $this->method;
    }    
    
    public function getPrefixPattern(): string {
        
        return $this->prefixPattern;
    }
    
    public function getSuffixPattern(): string {
        
        return $this->suffixPattern;
    }
    
    public function apply(): self {
                
        //$tags = $this->getTags();

//        if($this->getName() == 'ObjectVector') {
//            
//            var_dump($fixture->getMacros());
//            var_dump($this);
//            die(' -- ObjectVector --');
//        }
        
        $tags = array_merge($this->getDefaults(), $this->getTags());
       
        
        $macros = [];
        
        if($this->getParent() === null || $this->getMethod() !== 'append') {
            
            $macros = $this->getMacros();
        }
  
        if($this->getParent() !== null && $this->getMethod() !== 'replace') {
        
            if(!array_key_exists($this->getParent()->getName(), static::getFixtures())) {

                throw new Exception("Trying to extend '{$this->getName()}' from '{$this->getParent()->getName()},' but it doesn't seem like '{$this->getParent()->getName()}' has been loaded?");
            }


            $parent = $this->getParent()->apply();               
            
            foreach($parent->getTags() as $tagName => $parentTag) {

                $childTag = null;



                if(array_key_exists($tagName, $this->getTags())) {

                    $childTag = $this->getTags()[$tagName];                                                       
                }
                
                if($childTag === null && array_key_exists($tagName, $this->getDefaults())) {                                                      

                    $childTag = $this->getDefaults()[$tagName];
                }
                
                if($childTag === null && $this->getParent() !== null && array_key_exists($tagName, $this->getParent()->getDefaults())) {

                    $childTag = $this->getParent()->getDefaults()[$tagName];
                }                
                

//                if($this->getName() == 'IRenderableMapBase' && $tagName == 'namespace') {
//  
//                    print_r($childTag);
////                    print_r($this->getTags());
////                    print_r($this->getDefaults());
//                    exit;
//                }                


                $tag = [
                    'content' => [],
                    'method' => $parentTag['method'],
                    'filters' => $parentTag['filters']
                ];

                if($childTag !== null) {

                    //$tag['method'] = ($childTag['method'] !== null ? $childTag['method'] : $parentTag['method']);                        
                    //$tag['filters'] = ($childTag['filters'] !== null ? $childTag['filters'] : $parentTag['filters']);

                    $tag['method'] = $childTag['method'];
                    $tag['filters'] = $childTag['filters'];

    //                        if(count($childTag['filters']) > 0 && count($parentTag['filters']) > 0) {
    //                            echo "-----------------------------------------------\n";
    //                            var_dump($childTag['filters'], $parentTag['filters'], $tag['filters']);
    //                        }
                }

                if(($childTag !== null && ($childTag['method'] === 'parent')) || $childTag === null) {

                    // Use the parent value regardless

                    foreach($parentTag['content'] as $parentContent) {

                        $tag['content'][] = static::applyFilters($parentContent, ($parentTag['filters'] === null ? [] : $parentTag['filters']));
                    }

                } else {

                    if($childTag['method'] === 'child' || $childTag['method'] === null) {

                        // Replace the parent value with the child value

                        $tag['content'] = $childTag['content'];                            
                    }

                    if($childTag['method'] === 'prepend') {

                        // Prepend the child value to the parent value

                        foreach($childTag['content'] as $childContent) {

                            $tag['content'][] = $childContent;
                        }   
                    }

                    if($childTag['method'] !== 'child' && $childTag['method'] !== null) {

                        foreach($parentTag['content'] as $parentContent) {

                            $tag['content'][] = static::applyFilters($parentContent, ($parentTag['filters'] === null ? [] : $parentTag['filters']));
                        }

                    }

                    if($childTag['method'] === 'append') {

                        // Append the child value to the parent value

                        foreach($childTag['content'] as $childContent) {

                            $tag['content'][] = $childContent;
                        }                        

                    }   
                }

                //if($tagName == 'class' && $this->getName() == 'ObjectBase') {
                //    var_dump($tag);                        
                //}

                $tags[$tagName] = $tag;
    //                    
    //                    if($childTag['method'] === 'child') {
    //                        print_r($tag['content']);
    //                    }
            }
            
            foreach($parent->getMacros() as $parentMacro) {

                $macros[] = $parentMacro;
            }                
            
            if($this->getMethod() === 'append') {

                foreach($this->getMacros() as $thisMacro) {

                    $macros[] = $thisMacro;
                }
            }
            
      
            
////  //FIXME        
//if($this->getName() == 'IXmlDocumentNodeVectorBase') {
//
//    echo "\n\n";
//    
//    var_dump($parent->getParent()->apply()->getParent()->apply()->getName());
//    var_dump($parent->getParent()->apply()->getName());
//    var_dump($parent->apply()->getName());
//
//    exit;
//}                    
        }        
        
//if($this->getName() == 'IXmlDocumentNodeVectorBase') {            
////if($this->getName() == 'xml-document-node-vector-base-interface') {
//
//    echo "\n\n";
//        
//    var_dump($this->inherits);
//    var_dump($this->getParent());
//    var_dump($macros);
//
//    exit;
//}             
        
        return new self(
                $this->getName(),
                $tags,
                $this->getMethod(),
                ($this->getParent() !== null ? $this->getParent()->getName() : null),                
                //$this->getParent(),
                $this->getBase(),
                $this->getOutput(),
                $this->getWorking(),
                $this->getSuppressOutput(),
                $macros,
                $this->getDefaults(),
                $this->getPrefixPattern(),
                $this->getSuffixPattern()
            );

    }
    
    public function render(bool $apply = true): string {
         
        $fixture = $this;
        
        if($apply) {
            $fixture = $this->apply();
            
//  //FIXME  
//if($this->getName() == 'object-vector-base-interface') {
//
//    echo "\n\n";
//        
//    var_dump($this->getParent());
//    var_dump($this->getMethod());
//
//    exit;
//}             
//if($fixture->getName() == 'document-node-vector-base-interface') {
//
//    echo "\n\n";
//        
//    var_dump($fixture->getMacros());
//
//    exit;
//}                    
//if($fixture->getName() == 'xml-document-node-vector-base-interface') {
//
//    echo "\n\n";
//        
//    var_dump($fixture->getMacros());
//
//    exit;
//}            
//if($fixture->getName() == 'IXmlDocumentNodeVectorBase') {
//
//    echo "\n\n";
//        
//    var_dump($fixture->getMacros());
//
//    exit;
//}          
        }
        
        $output = '';
        
       
        foreach($fixture->getMacros() as $macro) {
            
            foreach($macro['content'] as $content) {
                
                $content = static::applyTags($content, $fixture->getTags(), $fixture->getDefaults(), $this->getPrefixPattern(), $this->getSuffixPattern(), $this);
                
                $output .= static::applyFilters($content, $macro['filters']);

            }
        }
        
        $output = static::applyTags($output, $fixture->getTags(), $fixture->getDefaults(), $this->getPrefixPattern(), $this->getSuffixPattern(), $this);
        
        return $output;
    }
    
    public function generate(): array {
        
        $result = [];
        
        $output = $this->render();
        
        $inputDirArray = explode(DIRECTORY_SEPARATOR, static::$inputDir);
        $baseDirArray = explode(DIRECTORY_SEPARATOR, static::$baseDir);
        
        $diffDirArray = [];
        
        foreach($baseDirArray as $i) {
            
            if(in_array($i, $inputDirArray)) {
                
                $diffDirArray[] = $i;
            }
        }
        
        $commonDir = join(DIRECTORY_SEPARATOR, $diffDirArray);
        

        $outputPath = static::$baseDir . str_replace(static::$inputDir, '', $this->output);
        
//        if($this->getName() === 'ThemeVector') {
//           
//            echo "CWD: " . $this->getWorking() . "\n";
//            echo "BASE: " . static::$baseDir . "\n";
//            echo "INPUT: " . static::$inputDir . "\n";
//            die($outputPath);
//        }
        
        if(strpos($this->output, $commonDir) !== 0) {
            
            $result[] = "Skipped (macro is not within a common input/base path: {$commonDir}).";
            return $result;            
        }           
        
        if(empty(trim($output)) && $this->getOutput() !== null) {
            
            $result[] = "Macro '{$this->getName()}' output is empty? (output length: " . strlen($output) . "; " . count($this->getMacros()) . " macros).";
            
            if(count($this->getMacros()) === 0) {
                
                $result[] = "'{$this->getName()}' contains no macros (inherited or otherwise).";
            }
        }            

        if($this->output === null) {
            
            $result[] = "Cannot write macro '{$this->getName()}' - no output path specified.";
            return $result;
        }        
        
//        echo("\n   INPUT - TEMPLATES ROOT: " . static::$inputDir . "\n    BASE - TARGET ROOT: " . static::$baseDir . "\n    COMMON DIR STRING: {$commonDir}\n");
        
        
        if(!is_dir($commonDir)) {

            $result[] = "Creating directory '{$commonDir}'";
            
            
            //print_r($outputDir);
            
            if(!mkdir($commonDir, null, true)) {
            
                throw new Exception("Cannot create output directory for macro '{$this->getName()}'");
            }
        }        
        
        file_put_contents($outputPath, $output);

        $result[] = "OK ('{$outputPath}' - " . strlen($output) . " characters).";
        
        return $result;
    }    

}
