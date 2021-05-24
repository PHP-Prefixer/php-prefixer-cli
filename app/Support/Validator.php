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

use Github\Client as GithubClient;
use Illuminate\Support\Facades\File;

class Validator
{
    public function validateDirectoryExists($param)
    {
        if (!File::exists($param)) {
            return false;
        }

        if (!File::isDirectory($param)) {
            return false;
        }

        if (empty(File::files($param))) {
            return false;
        }

        return (bool) realpath($param);
    }

    public function validateDirectoryEmpty($param)
    {
        if (File::exists($param)) {
            if (!File::isDirectory($param)) {
                return false;
            }

            if (!empty(File::files($param))) {
                return false;
            }

            return true;
        }

        mkdir($this->targetPath, 0700, true);

        if (!File::exists($param)) {
            return false;
        }

        return true;
    }

    public function validatePAT($personalAccessToken)
    {
        $prefixerClient = (new PrefixerClient())->authenticate($personalAccessToken);

        return $prefixerClient->isAuthenticated();
    }

    public function validateProject($personalAccessToken, $projectId)
    {
        try {
            $prefixerClient = (new PrefixerClient())->authenticate($personalAccessToken);
            $prefixerClient->project($projectId);

            return true;
        } finally {
            return false;
        }
    }

    public function validateGAT($githubAccessToken)
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
