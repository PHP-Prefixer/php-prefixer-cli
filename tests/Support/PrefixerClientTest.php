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

    public function testCreateBuild()
    {
        $projectId = (int) env('PROJECT_ID');
        $sourceDirectory = env('SOURCE_DIRECTORY');
        $projectZip = realpath($sourceDirectory.'/../Source.zip');
        $response = $this->apiClient()->createBuild($projectId, $projectZip);

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
        $response = $this->apiClient()->download($projectId, $buildId);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(\in_array('application/x-zip', $response->getHeader('Content-Type'), true));

        $this->expectExceptionCode(404);
        $this->apiClient()->download($projectId, 404);
    }

    public function testLogDownload()
    {
        $projectId = (int) env('PROJECT_ID');
        $buildId = (int) env('TEST_BUILD_ID');

        $tmpFile = tempnam(getcwd(), 'ppp');
        $response = $this->apiClient()->downloadLog($projectId, $buildId, $tmpFile);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(\in_array('application/x-zip', $response->getHeader('Content-Type'), true));

        $contentDisposition = $response->getHeader('Content-Disposition');
        list(, $filename) = explode('filename=', $contentDisposition[0]);
        $this->assertStringEndsWith('.log.zip', $filename);

        unlink($tmpFile);

        $this->expectExceptionCode(404);
        $this->apiClient()->downloadLog($projectId, 404, $tmpFile);
    }

    private function apiClient()
    {
        $personalAccessToken = env('PERSONAL_ACCESS_TOKEN');

        return (new PrefixerClient())->authenticate($personalAccessToken);
    }
}
