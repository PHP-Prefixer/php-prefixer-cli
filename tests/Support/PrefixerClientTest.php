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

namespace Tests\Support;

use App\Support\PrefixerClient;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class PrefixerClientTest extends TestCase
{
    public function testIsAuthenticated()
    {
        $this->assertTrue($this->apiClient()->IsAuthenticated());

        $personalAccessToken = env('INVALID_PERSONAL_ACCESS_TOKEN');
        $prefixerClient = (new PrefixerClient())->authenticate($personalAccessToken);
        $this->expectExceptionCode(401);
        $prefixerClient->IsAuthenticated();
    }

    public function testUser()
    {
        $response = $this->apiClient()->user();
        $this->assertGreaterThan(0, $response->user->id);
    }

    public function testProject()
    {
        $projectId = (int) env('PROJECT_ID');

        $response = $this->apiClient()->project($projectId);
        $this->assertSame($projectId, $response->project->id);

        $this->expectExceptionCode(404);
        $this->apiClient()->project(404);
    }

    /**
     * @group disabled
     */
    public function testCreateBuild()
    {
        $projectId = (int) env('PROJECT_ID');
        $sourceDirectory = env('SOURCE_DIRECTORY');
        $githubAccessToken = env('GITHUB_ACCESS_TOKEN');
        $projectZip = realpath($sourceDirectory.'/../Source.zip');

        $response = $this->apiClient()->createBuild($projectId, $projectZip, $githubAccessToken);

        $this->assertSame('initial-state', $response->build->state);
    }

    public function testBuild()
    {
        $projectId = (int) env('PROJECT_ID');
        $buildId = (int) env('TEST_BUILD_ID');
        $response = $this->apiClient()->build($projectId, $buildId);

        $this->assertSame($buildId, $response->build->id);

        $this->expectExceptionCode(404);
        $this->apiClient()->build($projectId, 404);
    }

    public function testDownload()
    {
        $projectId = (int) env('PROJECT_ID');
        $buildId = (int) env('TEST_BUILD_ID');
        $targetDirectory = env('TARGET_DIRECTORY');
        $downloadedFile = $this->apiClient()->download($projectId, $buildId, $targetDirectory);

        $this->assertStringEndsWith('.zip', $downloadedFile);
        $this->fileExists($downloadedFile);
        unlink($downloadedFile);

        $this->expectExceptionCode(404);
        $this->apiClient()->download($projectId, 404, $targetDirectory);
    }

    public function testLogDownload()
    {
        $projectId = (int) env('PROJECT_ID');
        $buildId = (int) env('TEST_BUILD_ID');
        $targetDirectory = env('TARGET_DIRECTORY');

        $downloadedFile = $this->apiClient()->downloadLog($projectId, $buildId, $targetDirectory);

        $this->assertStringEndsWith('.log.zip', $downloadedFile);
        $this->fileExists($downloadedFile);
        unlink($downloadedFile);

        $this->expectExceptionCode(404);
        $this->apiClient()->download($projectId, 404, $targetDirectory);
    }

    public function testDeleteBuild()
    {
        $projectId = (int) env('PROJECT_ID');
        $sourceDirectory = env('SOURCE_DIRECTORY');
        $githubAccessToken = env('GITHUB_ACCESS_TOKEN');
        $projectZip = realpath($sourceDirectory.'/../Source-No-Composer.zip');

        $response = $this->apiClient()->createBuild($projectId, $projectZip, $githubAccessToken);
        $build = $response->build;

        $this->assertSame('initial-state', $build->state);

        $response = $this->apiClient()->deleteBuild($projectId, $build->id);
        $this->assertSame(204, $response->getStatusCode());

        $this->expectExceptionCode(404);
        $this->apiClient()->build($projectId, $build->id);
    }

    private function apiClient()
    {
        $personalAccessToken = env('PERSONAL_ACCESS_TOKEN');

        return (new PrefixerClient())->authenticate($personalAccessToken);
    }
}
