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
    const WAIT_TIMEOUT = 15;
    private $personalAccessToken;

    private $prefixerClient;

    public function __construct($personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;
        $this->prefixerClient = (new PrefixerClient())->authenticate($this->personalAccessToken);
    }

    public function run($sourcePath, $targetPath, $projectId, $githubAccessToken = null)
    {
        $tmpZip = $this->temporaryZipFilename($sourcePath, $targetPath, $projectId, $githubAccessToken);

        $zipManager = new ZipManager();
        $zipManager->compress($this->sourcePath, $tmpZip);

        $build = $this->prefixerClient->createBuild($tmpZip, $projectId, $this->githubAccessToken);
        $build = $this->waitForProcessing($build);
        $downloadedZip = $this->download($build);
        $zipManager->uncompress($downloadedZip, $this->targetPath);
    }

    private function temporaryZipFilename()
    {
        return tempnam($this->targetPath, 'PPP').'.zip';
    }

    private function waitForProcessing($build)
    {
        while ($updatedBuild = $this->isProcessing($build)) {
            sleep(self::WAIT_TIMEOUT);
        }

        return $updatedBuild;
    }

    private function isProcessing($build)
    {
        $nuild = $this->prefixerClient->build($build->project_id, $build->build_id);

        return !\in_array($build->state, ['success', 'failed'], true);
    }

    private function download($build)
    {
        if ('success' === $build->state) {
            return $this->prefixerClient->download($build->project_id, $build->build_id);
        }

        // Failed
        return $this->prefixerClient->downloadLog($build->project_id, $build->build_id);
    }
}
