<?php
/*
 * See license information at the package root in LICENSE.md
 */
namespace ion\Packages;

/**
 * Description of PackageException
 *
 * @author Justus
 */
use Exception;

class PackageException extends Exception
{
    public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}