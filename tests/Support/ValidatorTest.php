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
    public function testIsValidSourceDirectory()
    {
        $validator = new Validator();
        $this->assertTrue($validator->isValidSourceDirectory(env('SOURCE_DIRECTORY')));
        $this->assertFalse($validator->isValidSourceDirectory('/nono'));
    }

    public function testIsValidTargetDirectory()
    {
        $this->cleanTargetDirectory();

        $validator = new Validator();
        $targetDirectory = env('TARGET_DIRECTORY');
        $this->assertTrue(File::exists($targetDirectory));
        $this->assertEmpty(File::allFiles($targetDirectory));

        $this->assertTrue($validator->isValidTargetDirectory($targetDirectory));

        touch($targetDirectory.'/test');
        $this->assertFalse($validator->isValidTargetDirectory($targetDirectory));
        unlink($targetDirectory.'/test');

        rmdir($targetDirectory);
        $this->assertTrue($validator->isValidTargetDirectory($targetDirectory));
        $this->assertTrue(File::exists($targetDirectory));
    }

    public function testIsPersonalAccessToken()
    {
        $validator = new Validator();

        $pat = env('PERSONAL_ACCESS_TOKEN');
        $this->assertTrue($validator->isPersonalAccessToken($pat));

        $pat = env('INVALID_PERSONAL_ACCESS_TOKEN');
        $this->assertFalse($validator->isPersonalAccessToken($pat));
    }

    public function testIsValidProjectId()
    {
        $validator = new Validator();

        $pat = env('PERSONAL_ACCESS_TOKEN');
        $projectId = env('PROJECT_ID');

        $this->assertTrue($validator->isValidProjectId($pat, $projectId));
        $this->assertFalse($validator->isValidProjectId($pat, 404));
    }

    public function testIsValidGithubAccessToken()
    {
        $validator = new Validator();

        $gat = env('GITHUB_ACCESS_TOKEN');
        $this->assertTrue($validator->isValidGithubAccessToken($gat));

        $gat = env('INVALID_GITHUB_ACCESS_TOKEN');
        $this->assertFalse($validator->isValidGithubAccessToken($gat));
    }
}
