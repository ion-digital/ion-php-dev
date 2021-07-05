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
    
    function getFilename(): ?string;
    
    function execute(OutputInterface $output): int;    
    
    function getInputObjects(): array;
    
    function getOutputDirectory(): string;
    
    public function getInstanceKey(): string;
    
    public function getPath(): string;
    
    function isDownloaded(): bool;
    
    function download(bool $ignoreCert): void;
}
