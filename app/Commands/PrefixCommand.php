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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        {--delete-build : Delete the build after download}
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
        $start = microtime(true);

        $validator = new Validator();
        $sourceDirectory = realpath($this->argument('source-directory'));

        if (!$validator->isValidSourceDirectory($sourceDirectory)) {
            $this->error("{$sourceDirectory} not found");

            return 1;
        }

        $targetDirectory = realpath($this->argument('target-directory'));

        if (!$validator->isValidTargetDirectory($targetDirectory)) {
            $this->error("{$targetDirectory} not found");

            return 1;
        }

        $personalAccessToken = $this->argument('personal-access-token');

        if (!$validator->isPersonalAccessToken($personalAccessToken)) {
            $this->error(
                'The Personal Access Token is invalid. Please, generate a new token on https://php-prefixer.com.'
            );

            return 1;
        }

        $projectId = (int) $this->argument('project-id');

        if (!$validator->isValidProjectId($personalAccessToken, $projectId)) {
            $this->error(
                'The Project ID is invalid'
            );

            return 1;
        }

        $githubAccessToken = $this->option('github-access-token');

        if ($githubAccessToken && !$validator->isValidGithubAccessToken($githubAccessToken)) {
            $this->error(
                'The Github Access Token is invalid'
            );

            return 1;
        }

        $deleteBuild = $this->hasOption('delete-build');

        $processor = new Processor($personalAccessToken);
        $build = $processor->run($sourceDirectory, $targetDirectory, $projectId, $githubAccessToken);

        if ($deleteBuild) {
            $processor->deleteBuild($projectId, $build->id);
        }

        return $this->renderOutput($build->state, $start);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->argumentOrEnv($input, 'source-directory');
        $this->argumentOrEnv($input, 'target-directory');
        $this->argumentOrEnv($input, 'personal-access-token');
        $this->argumentOrEnv($input, 'project-id');
        $this->optionOrEnv($input, 'github-access-token');
        $this->optionOrEnv($input, 'delete-build');
    }

    private function argumentOrEnv($input, $key)
    {
        if (!$input->hasArgument($key) || null === $input->getArgument($key)) {
            $input->setArgument($key, env(Str::upper(Str::snake($key))));
        }
    }

    private function optionOrEnv($input, $key)
    {
        if (!$input->hasOption($key) || null === $input->getOption($key)) {
            $input->setOption($key, env(Str::upper(Str::snake($key))));
        }
    }

    private function renderOutput($state, $start)
    {
        $processingTime = round(microtime(true) - $start, 2);
        $formattedProcessingTime = '  -- Processing time: '.number_format($processingTime).' seconds';

        switch ($state) {
            case 'success':
                $this->info('Project prefixed successfully.');
                $this->info($formattedProcessingTime);

                return 0;
            case 'cancelled':
                $this->error('Project prefixing cancelled.');
                $this->info($formattedProcessingTime);

                return 1;
            case 'failed':
                $this->error('Project prefixing failed.');
                $this->info($formattedProcessingTime);

                return 1;
        }

        $this->error('Project prefixing error. ('.$state.')');
        $this->info($formattedProcessingTime);

        return 1;
    }
}
