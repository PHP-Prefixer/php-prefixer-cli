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

namespace App\Support;

class Processor
{
    public const WAIT_TIMEOUT = 30;

    private $personalAccessToken;

    private $prefixerClient;

    public function __construct($personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;
        $this->prefixerClient = (new PrefixerClient())->authenticate($this->personalAccessToken);
    }

    public function run($sourcePath, $targetPath, int $projectId, $githubAccessToken = null, $includeVendor = false, $includeAll = false)
    {
        $zipManager = new ZipManager();
        $zipManager->includeVendor( $includeVendor );
        $zipManager->includeAll( $includeAll );

        $tmpZip = $this->temporaryZipFilename($targetPath);
        $zipManager->compress($sourcePath, $tmpZip);

        $response = $this->prefixerClient->createBuild($projectId, $tmpZip, $githubAccessToken);
        unlink($tmpZip);

        $build = $response->build;
        $this->waitForProcessing($build);
        $response = $this->prefixerClient->build($build->project_id, $build->id);

        $build = $response->build;
        $downloadedZip = $this->download($build, $targetPath);

        if ($downloadedZip) {
            $zipManager->uncompress($downloadedZip, $targetPath);
            unlink($downloadedZip);
        }

        return $build;
    }

    public function deleteBuild(int $projectId, int $buildId)
    {
        return $this->prefixerClient->deleteBuild($projectId, $buildId);
    }

    private function temporaryZipFilename($targetPath)
    {
        return $targetPath.'/'.basename($targetPath).'.zip';
    }

    private function waitForProcessing($build)
    {
        while ($this->isProcessing($build)) {
            sleep(self::WAIT_TIMEOUT);
        }
    }

    private function isProcessing($build)
    {
        $response = $this->prefixerClient->build($build->project_id, $build->id);
        $build = $response->build;

        return !\in_array($build->state, ['success', 'failed', 'cancelled'], true);
    }

    private function download($build, $targetPath)
    {
        if ('success' === $build->state) {
            if (isset($build->build_target_project_file)) {
                return $this->prefixerClient->download($build->project_id, $build->id, $targetPath);
            }

            return $this->prefixerClient->downloadLog($build->project_id, $build->id, $targetPath);
        }

        if ('failed' === $build->state) {
            return $this->prefixerClient->downloadLog($build->project_id, $build->id, $targetPath);
        }

        // cancelled
        return null;
    }
}
