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

namespace App\Commands;

use App\Support\Processor;
use App\Support\Validator;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;

class PrefixCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'prefix
        {source-directory : Project source directory}
        {target-directory : Project target directory}
        {personal-access-token : Personal Access Token generates on https://php-prefixer.com}
        {project-id : The project ID to process the source code}
        {--github-access-token= : Github access token for private repositories}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Prefix a PHP project';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $appName = config('app.name');
        $invalidPersonalAccessToken = env('INVALID_PERSONAL_ACCESS_TOKEN');

        $validator = new Validator();
        $sourceDirectory = $this->argumentOrEnv('source-directory');

        if (!$sourcePath = $validator->isValidSourceDirectory($sourceDirectory)) {
            $this->error("{$sourceDirectory} not found");

            return 1;
        }

        $targetPath = $this->argumentOrEnv('target-directory');

        if (!$sourcePath = $validator->isValidTargetDirectory($sourceDirectory)) {
            $this->error("{$sourceDirectory} not found");

            return 1;
        }

        if (!$personalAccessToken = $validator->isPersonalAccessToken($this->argumentOrEnv('personal-access-token'))) {
            $this->error(
                'The Personal Access Token is invalid. Please, generate a new token on https://php-prefixer.com.'
            );

            return 1;
        }

        if (!$projectId = $validator->isValidProjectId($personalAccessToken, (int) $this->argumentOrEnv('project-id'))) {
            $this->error(
                'The Project ID is invalid'
            );

            return 1;
        }

        $githubAccessToken = $this->optionOrEnv('github-access-token');

        if ($githubAccessToken && !$validator->isValidGithubAccessToken($githubAccessToken)) {
            $this->error(
                'The Github Access Token is invalid'
            );

            return 1;
        }

        $processor = new Processor($personalAccessToken);
        $processor->run($sourcePath, $targetPath, $projectId, $githubAccessToken);

        $this->info('Project prefixed successfully');

        return 0;
    }

    private function argumentOrEnv($key)
    {
        if ($value = $this->argument($key)) {
            return $value;
        }

        return env($key);
    }

    private function optionOrEnv($key)
    {
        if ($value = $this->option($key)) {
            return $value;
        }

        return env(Str::upper(Str::snake($key)));
    }
}
