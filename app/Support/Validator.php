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
use Github\Client as GithubClient;
use Illuminate\Support\Facades\File;

class Validator
{
    public function isValidSourceDirectory(string $param): bool
    {
        if (!File::exists($param)) {
            return false;
        }

        if (!File::isDirectory($param)) {
            return false;
        }

        if (empty(File::allFiles($param))) {
            return false;
        }

        return (bool) realpath($param);
    }

    public function isValidTargetDirectory(string $param): bool
    {
        if (File::exists($param)) {
            if (!File::isDirectory($param)) {
                return false;
            }

            if (!empty(File::allFiles($param))) {
                return false;
            }

            return true;
        }

        mkdir($param, 0700, true);

        if (!File::exists($param)) {
            return false;
        }

        return true;
    }

    public function isPersonalAccessToken(string $personalAccessToken): bool
    {
        try {
            $prefixerClient = (new PrefixerClient())->authenticate($personalAccessToken);

            return $prefixerClient->isAuthenticated();
        } catch (Exception $e) {
            return false;
        }
    }

    public function isValidProjectId(string $personalAccessToken, int $projectId): bool
    {
        try {
            $prefixerClient = (new PrefixerClient())->authenticate($personalAccessToken);
            $response = $prefixerClient->project($projectId);

            return $projectId === $response->project->id;
        } catch (Exception $e) {
            return false;
        }
    }

    public function isValidGithubAccessToken(string $githubAccessToken): bool
    {
        try {
            $githubClient = new GithubClient();
            $githubClient->authenticate($githubAccessToken, null, GithubClient::AUTH_ACCESS_TOKEN);
            $user = $githubClient->api('me');
            $me = $user->show();

            return $me['id'] > 0;
        } catch (\Exception $e) {
        }

        return false;
    }
}
