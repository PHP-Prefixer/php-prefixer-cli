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

use App\Support\Processor;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class ProcessorTest extends TestCase
{
    public function testRun()
    {
        $this->cleanTargetDirectory();

        $personalAccessToken = env('PERSONAL_ACCESS_TOKEN');
        $processor = new Processor($personalAccessToken);

        $sourceDirectory = env('SOURCE_DIRECTORY');
        $targetDirectory = env('TARGET_DIRECTORY');
        $projectId = (int) env('PROJECT_ID');
        $githubAccessToken = env('GITHUB_ACCESS_TOKEN');

        $build = $processor->run($sourceDirectory, $targetDirectory, $projectId, $githubAccessToken);

        $this->assertSame('success', $build->state);
        $this->cleanTargetDirectory();
    }

    public function testCancelledRun()
    {
        $this->cleanTargetDirectory();

        $personalAccessToken = env('PERSONAL_ACCESS_TOKEN');
        $processor = new Processor($personalAccessToken);

        $sourceDirectory = env('BROKEN_COMPOSER_SOURCE_DIRECTORY');
        $targetDirectory = env('TARGET_DIRECTORY');
        $projectId = (int) env('PROJECT_ID');
        $githubAccessToken = env('GITHUB_ACCESS_TOKEN');

        $build = $processor->run($sourceDirectory, $targetDirectory, $projectId, $githubAccessToken);

        $this->assertSame('cancelled', $build->state);
        $this->assertSame('Prefixer schema definition error.', $build->state_message);
        $this->cleanTargetDirectory();
    }

    /**
     * @group disabled
     */
    public function testFailedRun()
    {
        $this->cleanTargetDirectory();

        $personalAccessToken = env('PERSONAL_ACCESS_TOKEN');
        $processor = new Processor($personalAccessToken);

        $sourceDirectory = env('BROKEN_DEPS_SOURCE_DIRECTORY');
        $targetDirectory = env('TARGET_DIRECTORY');
        $projectId = (int) env('PROJECT_ID');

        $build = $processor->run($sourceDirectory, $targetDirectory, $projectId);

        $this->assertSame('failed', $build->state);
    }
}
