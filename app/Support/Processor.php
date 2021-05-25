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
    const WAIT_TIMEOUT = 30;
    private $personalAccessToken;

    private $prefixerClient;

    public function __construct($personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;
        $this->prefixerClient = (new PrefixerClient())->authenticate($this->personalAccessToken);
    }

    public function run($sourcePath, $targetPath, int $projectId, $githubAccessToken = null)
    {
        $zipManager = new ZipManager();
        $tmpZip = $this->temporaryZipFilename($targetPath);
        $zipManager->compress($sourcePath, $tmpZip);

        $response = $this->prefixerClient->createBuild($projectId, $tmpZip, $githubAccessToken);

        $build = $response->build;
        unlink($tmpZip);

        $this->waitForProcessing($build);
        $downloadedZip = $this->download($build, $targetPath);
        $zipManager->uncompress($downloadedZip, $targetPath);
        unlink($downloadedZip);

        $response = $this->prefixerClient->build($build->project_id, $build->id);

        return $build;
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

        return !\in_array($build->state, ['success', 'failed'], true);
    }

    private function download($build, $targetPath)
    {
        if ('success' === $build->state) {
            return $this->prefixerClient->download($build->project_id, $build->id, $targetPath);
        }

        // Failed
        return $this->prefixerClient->downloadLog($build->project_id, $build->id, $targetPath);
    }
}
