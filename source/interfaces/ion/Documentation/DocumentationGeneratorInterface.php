<?php

/*
 * See license information at the package root in LICENSE.md
 */

namespace ion\Dev\Documentation;

/**
 *
 * @author Justus
 */

use Symfony\Component\Console\Output\OutputInterface;

interface DocumentationGeneratorInterface {
    
    function getUri(): string;
    
    function getBinaryFilename(): ?string;
    
    function getBinaryPath(): string;
    
    function getProjectFilename(): ?string;
    
    function prepareCommand(): string;
    
    function execute(OutputInterface $output, bool $ignoreSslCert = false): int;    
    
    function getInputObjects(): array;
    
    function getOutputDirectory(): string;
    
    function getInstanceKey(): string;
    
    function isBinaryDownloaded(): bool;
    
    function downloadBinary(bool $ignoreCert): void;
}
