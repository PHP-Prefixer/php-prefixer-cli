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
use Psr\Http\Message\ResponseInterface;

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
        $user = $this->user();

        return $user->id > 0;
    }

    public function user()
    {
        return $this->request('GET', 'user');
    }

    public function project($projectId)
    {
        return $this->request('GET', 'projects/'.$projectId);
    }

    public function createBuild($projectId, $zipFilename, $githubAccessToken = null)
    {
        $options = [
            'multipart' => [
                [
                    'name' => 'uploaded_source_file',
                    'filename' => basename($zipFilename),
                    'contents' => Utils::tryFopen($zipFilename, 'r'),
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
            'projects/'.$projectId.'/build',
            $options
        );
    }

    public function build($projectId, $buildId)
    {
        return $this->request(
            'GET',
            'projects/'.$projectId.'/builds/'.$buildId
        );
    }

    public function download($projectId, $buildId)
    {
        return $this->request(
            'GET',
            'projects/'.$projectId.'/builds/'.$buildId.'/download'
        );
    }

    public function downloadLog($projectId, $buildId)
    {
        return $this->request(
            'GET',
            'projects/'.$projectId.'/builds/'.$buildId.'/download/log'
        );
    }

    private function request(string $method, $relApiUri = '', array $options = []): ResponseInterface
    {
        $options = array_merge($this->jsonHeaders(), $options);

        $res = (new GuzzleClient())->request(
            'GET',
            self::API_BASE_URI.$relApiUri,
            $options,
        );

        if (200 === $res->getStatusCode()) {
            return json_decode($res->getBody());
        }

        throw new Exception($res->getReasonPhrase(), $res->getStatusCode());
    }

    private function jsonHeaders()
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json, text/plain, */*',
                'Authorization' => 'Bearer '.$this->personalAccessToken,
            ],
        ];
    }
}
