<?php

/*
 * @package     PHP Prefixer REST API CLI
 *
 * @author      Desarrollos Inteligentes Virtuales, SL. <team@div.com.es>
 * @copyright   Copyright (c)2019-2022 Desarrollos Inteligentes Virtuales, SL. All rights reserved.
 * @license     MIT
 *
 * @see         https://php-prefixer.com
 */

namespace App\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Utils;

class PrefixerClient
{
    public const API_BASE_URI = 'https://php-prefixer.com/api/v1';

    private $personalAccessToken;

    public function authenticate($personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;

        return $this;
    }

    public function isAuthenticated(): bool
    {
        $response = $this->user();

        return $response->user->id > 0;
    }

    public function user()
    {
        return $this->request('GET', '/user');
    }

    public function project(int $projectId)
    {
        return $this->request('GET', '/projects/'.$projectId);
    }

    public function createBuild(int $projectId, $zipFile, $githubAccessToken = null)
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'uploaded_source_file',
                    'filename' => basename($zipFile),
                    'contents' => Utils::tryFopen($zipFile, 'r'),
                ],
            ],
        ];

        if ($githubAccessToken) {
            $options['multipart'][] = [
                'name' => 'github_access_token',
                'contents' => $githubAccessToken,
            ];
        }

        return $this->request(
            'POST',
            '/projects/'.$projectId.'/builds',
            $options
        );
    }

    public function build(int $projectId, int $buildId)
    {
        return $this->request(
            'GET',
            '/projects/'.$projectId.'/builds/'.$buildId
        );
    }

    public function deleteBuild(int $projectId, int $buildId)
    {
        return $this->request(
            'DELETE',
            '/projects/'.$projectId.'/builds/'.$buildId
        );
    }

    public function download(int $projectId, int $buildId, $targetDirectory)
    {
        return $this->downloadWithSink(
            '/projects/'.$projectId.'/builds/'.$buildId.'/download',
            $targetDirectory
        );
    }

    public function downloadLog(int $projectId, int $buildId, $targetDirectory)
    {
        return $this->downloadWithSink(
            '/projects/'.$projectId.'/builds/'.$buildId.'/download/log',
            $targetDirectory
        );
    }

    private function downloadWithSink($downloadUri, $targetDirectory)
    {
        $sink = $this->temporaryZipFilename($targetDirectory);

        try {
            $response = $this->request(
                'GET',
                $downloadUri,
                [
                    'sink' => $sink,
                ]
            );

            if (200 !== $response->getStatusCode() ||
                !\in_array('application/x-zip', $response->getHeader('Content-Type'), true)) {
                unlink($sink);

                throw new \Exception('Unexpected response', $response->getStatusCode());
            }

            $downloadedFile = $targetDirectory.'/'.$this->filename($response);
            rename($sink, $downloadedFile);
        } catch (\Exception $e) {
            unlink($sink);

            throw $e;
        }

        return $downloadedFile;
    }

    private function request(string $method, $relApiUri = '', array $options = [])
    {
        $options = array_merge($this->headers(), $options);

        $res = (new GuzzleClient())->request(
            $method,
            self::API_BASE_URI.$relApiUri,
            $options,
        );

        $statusCode = $res->getStatusCode();

        if (\in_array($statusCode, [200, 201, 202, 204], true)) {
            if (\in_array('application/json', $res->getHeader('Content-Type'), true)) {
                return json_decode($res->getBody());
            }

            return $res;
        }

        throw new \Exception($res->getReasonPhrase(), $res->getStatusCode());
    }

    private function headers()
    {
        return [
            'headers' => [
                'Accept' => 'application/json, text/plain, */*',
                'Authorization' => 'Bearer '.$this->personalAccessToken,
            ],
        ];
    }

    private function filename($response)
    {
        $contentDisposition = $response->getHeader('Content-Disposition');
        [, $filename] = explode('filename=', $contentDisposition[0]);

        return $filename;
    }

    private function temporaryZipFilename($targetPath)
    {
        return tempnam($targetPath, 'PPP-');
    }
}
