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
use ion\Packages\Loader;

class Psr4Loader extends Psr0Loader
{
    protected function loadClass(string $className) : ?string
    {
        $path = realpath($this->getIncludePath()) . DIRECTORY_SEPARATOR . substr($className, strrpos($className, DIRECTORY_SEPARATOR)) . '.php';
        if (file_exists($path)) {
            include $path;
            return $path;
        }
        return parent::loadClass($className);
    }

}