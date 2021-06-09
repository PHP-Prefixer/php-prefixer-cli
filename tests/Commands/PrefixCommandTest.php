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

use Tests\TestCase;

/**
 * @coversNothing
 */
final class PrefixCommandTest extends TestCase
{
    public function testPrefix()
    {
        $this->cleanTargetDirectory();

        $this->artisan(
            'prefix',
            [
                'source-directory' => env('SOURCE_DIRECTORY'),
                'target-directory' => env('TARGET_DIRECTORY'),
                'personal-access-token' => env('PERSONAL_ACCESS_TOKEN'),
                'project-id' => env('PROJECT_ID'),
                '--github-access-token' => null,
                '--delete-build' => true,
            ]
        )
            ->expectsOutput('PHP-Prefixer: project prefixed successfully')
            ->assertExitCode(0);

        $this->assertFileExists(env('TARGET_DIRECTORY').'/composer.json');

        $this->cleanTargetDirectory();
    }

    public function testCancelledPrefix()
    {
        $this->cleanTargetDirectory();

        $this->artisan(
            'prefix',
            [
                'source-directory' => env('BROKEN_COMPOSER_SOURCE_DIRECTORY'),
                'target-directory' => env('TARGET_DIRECTORY'),
                'personal-access-token' => env('PERSONAL_ACCESS_TOKEN'),
                'project-id' => env('PROJECT_ID'),
                '--github-access-token' => null,
                '--delete-build' => true,
            ]
        )
            ->expectsOutput('PHP-Prefixer: project prefixing cancelled')
            ->assertExitCode(1);

        $this->cleanTargetDirectory();
    }
}
