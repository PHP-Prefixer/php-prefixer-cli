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
use NunoMaduro\LaravelDesktopNotifier\Contracts\Notification;
use NunoMaduro\LaravelDesktopNotifier\Contracts\Notifier;
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
        {--include-vendor : Include preinstalled vendor in the build}
        {--include-all : Include all files in the build instead of only php and composer files}
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
            $this->error("PHP-Prefixer: {$sourceDirectory} not found");

            return 1;
        }

        $targetDirectory = $this->argument('target-directory');

        if (!$validator->isValidTargetDirectory($targetDirectory)) {
            $this->error("PHP-Prefixer: {$targetDirectory} not found");

            return 1;
        }

        $personalAccessToken = $this->argument('personal-access-token');

        if (!$validator->isPersonalAccessToken($personalAccessToken)) {
            $this->error(
                'PHP-Prefixer: the Personal Access Token is invalid. Please, generate a new token on https://php-prefixer.com.'
            );

            return 1;
        }

        $projectId = (int) $this->argument('project-id');

        if (!$validator->isValidProjectId($personalAccessToken, $projectId)) {
            $this->error(
                'PHP-Prefixer: the Project ID is invalid'
            );

            return 1;
        }

        $githubAccessToken = $this->option('github-access-token');

        if ($githubAccessToken && !$validator->isValidGithubAccessToken($githubAccessToken)) {
            $this->error(
                'PHP-Prefixer: the Github Access Token is invalid'
            );

            return 1;
        }

        $deleteBuild = (bool) $this->option('delete-build');

        $includeVendor = (bool) $this->option('include-vendor');

        $includeAll = (bool) $this->option('include-all');

        $processor = new Processor($personalAccessToken);

        try {
            $build = $processor->run($sourceDirectory, $targetDirectory, $projectId, $githubAccessToken, $includeVendor, $includeAll);
        } catch (\GuzzleHttp\Exception\ClientException $clientException) {
            if (!$clientException->hasResponse()) {
                $this->error('PHP-Prefixer: '.$clientException->getMessage());

                return 1;
            }

            $response = $clientException->getResponse();
            $this->error('PHP-Prefixer: '.$response->getReasonPhrase());

            $body = json_decode($response->getBody());

            if (!$body) {
                return 1;
            }

            if (isset($body->message)) {
                $this->error('PHP-Prefixer: '.$body->message);
            }

            if (isset($body->errors->uploaded_source_file)) {
                $this->error('PHP-Prefixer: '.
                    implode(' ', $body->errors->uploaded_source_file));
            }

            return 1;
        }

        if ($deleteBuild) {
            $processor->deleteBuild($projectId, $build->id);
        }

        return $this->renderOutput($build, $start);
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
        $this->optionOrEnv($input, 'include-vendor');
        $this->optionOrEnv($input, 'include-all');
    }

    private function argumentOrEnv($input, $key)
    {
        if (!$input->hasArgument($key) || null === $input->getArgument($key)) {
            $value = env(Str::upper(Str::replace('-', '_', $key)));
            $input->setArgument($key, $value);
        }
    }

    private function optionOrEnv($input, $key)
    {
        if (!$input->hasOption($key) || null === $input->getOption($key)) {
            $value = env(Str::upper(Str::replace('-', '_', $key)));
            $input->setOption($key, $value);
        }
    }

    private function renderOutput($build, $start)
    {
        $processingTime = round(microtime(true) - $start, 2);
        $formattedProcessingTime = '  -- Processing time: '.number_format($processingTime).' seconds';

        $state = $build->state;

        switch ($state) {
            case 'success':
                $this->info('PHP-Prefixer: project prefixing completed');
                $this->notify('PHP-Prefixer CLI', 'Project prefixing completed');
                $this->info($formattedProcessingTime);

                return 0;
            case 'cancelled':
                $this->error('PHP-Prefixer: project prefixing cancelled');

                if (isset($build->state_message)) {
                    $this->error('PHP-Prefixer: '.$build->state_message);
                }

                $this->notify('PHP-Prefixer CLI', 'Project prefixing cancelled');
                $this->info($formattedProcessingTime);

                return 1;
            case 'failed':
                $this->error('PHP-Prefixer: project prefixing failed');

                if (isset($build->state_message)) {
                    $this->error('PHP-Prefixer: '.$build->state_message);
                }

                $this->notify('PHP-Prefixer CLI', 'Project prefixing failed');
                $this->info($formattedProcessingTime);

                return 1;
        }

        $this->error('PHP-Prefixer: project prefixing error ('.$state.')');
        $this->info($formattedProcessingTime);

        return 1;
    }

    private function notify($title, $body)
    {
        $notifier = app(Notifier::class);

        $notification = app(Notification::class)
            ->setTitle($title)
            ->setBody($body);

        $notification = $this->setIcon($notification);
        $notifier->send($notification);
    }

    private function setIcon($notification)
    {
        $icon = 'config/PHPPrefixer.png';

        return $notification->setIcon($this->extractIcon($icon));
    }

    private function extractIcon($icon)
    {
        $pharPath = \Phar::running(false);

        if (empty($pharPath)) {
            return $icon;
        }

        $phar = new \Phar($pharPath);
        $tmpDir = sys_get_temp_dir();
        $tmpIcon = $tmpDir.'/'.$icon;

        if (file_exists($tmpIcon)) {
            return $tmpIcon;
        }

        $phar->extractTo($tmpDir, $icon);

        return $tmpIcon;
    }
}
