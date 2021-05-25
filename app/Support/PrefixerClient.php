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

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Utils;

class PrefixerClient
{
    const API_BASE_URI = 'https://php-prefixer.com/api/v1';

    private $personalAccessToken;

    public function authenticate($personalAccessToken)
    {
        $this->personalAccessToken = $personalAccessToken;

        return $this;
    }

    public function isAuthenticated()
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

    public function download(int $projectId, int $buildId)
    {
        return $this->request(
            'GET',
            '/projects/'.$projectId.'/builds/'.$buildId.'/download'
        );
    }

    public function downloadLog(int $projectId, int $buildId, $file)
    {
        return $this->request(
            'GET',
            '/projects/'.$projectId.'/builds/'.$buildId.'/download/log',
            ['sink' => $file]
        );
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

        if (200 === $statusCode || 201 === $statusCode) {
            if (\in_array('application/json', $res->getHeader('Content-Type'), true)) {
                return json_decode($res->getBody());
            }

            return $res;
        }

        throw new Exception($res->getReasonPhrase(), $res->getStatusCode());
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
}
