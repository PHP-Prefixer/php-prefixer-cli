<?php

/*
 * @package     PHP Prefixer REST API CLI
 *
 * @author      Desarrollos Inteligentes Virtuales, SL. <team@div.com.es>
 * @copyright   Copyright (c)2019-2021 Desarrollos Inteligentes Virtuales, SL. All rights reserved.
 * @license     MIT
 *
 * @see         https://php-prefixer.com
 */

namespace Tests\Commands;

use App\Support\Validator;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class ValidatorTest extends TestCase
{
    public function testValidateDirectoryExists()
    {
        $this->assertTrue((new Validator())->validateDirectoryExists(env('SOURCE_DIRECTORY')));
        $this->assertFalse((new Validator())->validateDirectoryExists('/nono'));
    }

    public function testValidateDirectoryEmpty()
    {
        $targetDirectory = env('TARGET_DIRECTORY');
        $this->assertTrue(File::exists($targetDirectory));
        $this->assertEmpty(File::files($targetDirectory));

        $this->assertTrue((new Validator())->validateDirectoryEmpty($targetDirectory));

        touch($targetDirectory.'/test');
        $this->assertFalse((new Validator())->validateDirectoryEmpty($targetDirectory));
        unlink($targetDirectory.'/test');

        rmdir($targetDirectory);
        $this->assertTrue((new Validator())->validateDirectoryEmpty($targetDirectory));
        $this->assertTrue(File::exists($targetDirectory));
    }
}
