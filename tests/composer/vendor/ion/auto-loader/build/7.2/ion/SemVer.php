<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion;

/**
 * Description of SemVer
 *
 * @author Justus
 */

class SemVer implements ISemVer
{
    public static function create(int $major = 0, int $minor = 0, int $patch = 0, string $preRelease = null, array $metaData = null) : ISemVer
    {
        return new static($major, $minor, $patch, $preRelease, $metaData);
    }
    
    public static function parse(string $string) : ?ISemVer
    {
        $tokens = [];
        $pos = strpos($string, '+');
        if ($pos !== false) {
            $tmp = explode('.', substr($string, $pos + 1));
            if (count($tmp) > 0) {
                $tokens['meta-data'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        }
        $pos = strpos($string, '-');
        if ($pos !== false) {
            $tmp = substr($string, $pos + 1);
            if (strlen($tmp) > 0) {
                $tokens['pre-release'] = $tmp;
                $string = substr($string, 0, $pos);
            }
        }
        if (strpos(strtolower($string), 'v') === 0) {
            $string = substr($string, 1);
        }
        $tmp = explode('.', $string);
        if (count($tmp) > 0) {
            $tokens['version'] = $tmp;
        }
        $tokens = array_reverse($tokens);
        if (array_key_exists('version', $tokens)) {
            $version = $tokens['version'];
            $major = 0;
            $minor = 0;
            $patch = 0;
            $preRelease = null;
            $metaData = [];
            if (count($version) > 0) {
                if (!is_numeric($version[0])) {
                    return null;
                }
                $major = intval($version[0]);
            }
            if (count($version) > 1) {
                if (!is_numeric($version[1])) {
                    return null;
                }
                $minor = intval($version[1]);
            }
            if (count($version) > 2) {
                if (!is_numeric($version[2])) {
                    return null;
                }
                $patch = intval($version[2]);
            }
            if (array_key_exists('pre-release', $tokens)) {
                $preRelease = $tokens['pre-release'];
            }
            if (array_key_exists('meta-data', $tokens)) {
                $metaData = $tokens['meta-data'];
            }
            return new SemVer($major, $minor, $patch, $preRelease, $metaData);
        }
        return null;
    }
    
    public static function parsePackageJson(string $data) : ?ISemVer
    {
        $major = 0;
        $minor = 0;
        $patch = 1;
        $preRelease = null;
        $metaData = null;
        $json = json_decode($data, true);
        if ($json !== null) {
            if (isset($json['major'])) {
                $major = intval($json['major']);
            }
            if (isset($json['minor'])) {
                $minor = intval($json['minor']);
            }
            if (isset($json['patch'])) {
                $patch = intval($json['patch']);
            }
            if (isset($json['pre-release'])) {
                $preRelease = $json['pre-release'];
            }
            if (isset($json['meta-data'])) {
                foreach (array_values($json['meta-data']) as $value) {
                    $metaData[] = $value;
                }
            }
            return SemVer::create($major, $minor, $patch, $preRelease, $metaData);
        }
        return null;
    }
    
    public static function parseComposerJson(string $data) : ?ISemVer
    {
        $json = json_decode($data, true);
        if ($json !== null) {
            if (isset($json['version'])) {
                return SemVer::parse($json['version']);
            }
        }
        return null;
    }
    
    private $major = null;
    private $minor = null;
    private $patch = null;
    private $preRelease = null;
    private $metaData = [];
    public function __construct(int $major = 0, int $minor = 0, int $patch = 0, string $preRelease = null, array $metaData = null)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->preRelease = $preRelease;
        $this->metaData = $metaData === null ? [] : $metaData;
    }
    
    public function getMajor() : int
    {
        return $this->major;
    }
    
    public function getMinor() : int
    {
        return $this->minor;
    }
    
    public function getPatch() : int
    {
        return $this->patch;
    }
    
    public function getPreRelease() : ?string
    {
        return $this->preRelease;
    }
    
    public function getMetaData() : array
    {
        return $this->metaData;
    }
    
    public function toString() : string
    {
        $string = join('.', [$this->getMajor(), $this->getMinor(), $this->getPatch()]);
        if ($this->getPreRelease() !== null) {
            $string .= '-' . $this->getPreRelease();
        }
        if (count($this->getMetaData()) > 0) {
            $string .= '+' . join('.', $this->getMetaData());
        }
        return $string;
    }
    
    public function toVcsTag() : string
    {
        return 'v' . $this->toString();
    }
    
    public function toArray() : array
    {
        $array = [$this->getMajor(), $this->getMinor(), $this->getPatch()];
        if ($this->getPreRelease() !== null) {
            $array[] = $this->getPreRelease();
        }
        if (count($this->getMetaData()) > 0) {
            $array[] = $this->getMetaData();
        }
        return $array;
    }
    
    public function __toString() : string
    {
        return $this->toString();
    }
    
    public function isHigherThan(ISemVer $semVer) : bool
    {
        if ($this->getMajor() > $semVer->getMajor()) {
            return true;
        }
        if ($this->getMinor() > $semVer->getMinor()) {
            if ($this->getMajor() === $semVer->getMajor()) {
                return true;
            }
        }
        if ($this->getPatch() > $semVer->getPatch()) {
            if ($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                return true;
            }
        }
        return false;
    }
    
    public function isLowerThan(ISemVer $semVer) : bool
    {
        if ($this->getMajor() < $semVer->getMajor()) {
            return true;
        }
        if ($this->getMinor() < $semVer->getMinor()) {
            if ($this->getMajor() === $semVer->getMajor()) {
                return true;
            }
        }
        if ($this->getPatch() < $semVer->getPatch()) {
            if ($this->getMajor() === $semVer->getMajor() && $this->getMinor() === $semVer->getMinor()) {
                return true;
            }
        }
        return false;
    }
    
    public function isEqualTo(ISemVer $semVer) : bool
    {
        if ($this->getMajor() !== $semVer->getMajor()) {
            return false;
        }
        if ($this->getMinor() !== $semVer->getMinor()) {
            return false;
        }
        if ($this->getPatch() !== $semVer->getPatch()) {
            return false;
        }
        return true;
    }

}