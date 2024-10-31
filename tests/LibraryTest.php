<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateDiscovery(): void
    {
        $this->validateModule(__DIR__ . '/../Bring Gateway');
    }

    public function testValidateIO(): void
    {
        $this->validateModule(__DIR__ . '/../Bring Configurator');
    }

    public function testValidateConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../Bring List');
    }
}
