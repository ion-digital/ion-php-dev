<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages\Adapters;

/**
 * Description of PsrLoader
 *
 * @author Justus
 */
use ion\Packages\AutoLoader;

class Psr0Loader extends AutoLoader
{
    protected function loadClass(string $className) : ?string
    {
        $path = realpath($this->getIncludePath()) . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists($path)) {
            include $path;
            return $path;
        }
        return null;
    }

}