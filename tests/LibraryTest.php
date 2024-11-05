<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class LibraryTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateAccount(): void
    {
        $this->validateModule(__DIR__ . '/../Bring Account');
    }

    public function testValidateConfigurator(): void
    {
        $this->validateModule(__DIR__ . '/../Bring Configurator');
    }

    public function testValidateList(): void
    {
        $this->validateModule(__DIR__ . '/../Bring List');
    }
}
