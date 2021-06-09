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

namespace Tests\Commands;

use App\Support\ZipManager;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class ZipManagerTest extends TestCase
{
    public function testCompressUncompress()
    {
        $this->cleanTargetDirectory();

        $sourceDirectory = env('SOURCE_DIRECTORY');
        $tmpZip = getcwd().'/test.zip';

        $zipManager = new ZipManager();
        $status = $zipManager->compress($sourceDirectory, $tmpZip);

        $this->assertTrue($status);
        $this->assertFileExists($tmpZip);

        $targetDirectory = env('TARGET_DIRECTORY');
        $zipManager->uncompress($tmpZip, $targetDirectory);

        $this->assertFileExists($targetDirectory.'/app');
        $this->assertFileExists($targetDirectory.'/composer.json');

        $this->assertFileDoesNotExist($targetDirectory.'/vendor/composer/LICENSE');
        $this->assertFileDoesNotExist($targetDirectory.'/vendor/doctrine/inflector/phpstan.neon.dist');
        $this->assertFileDoesNotExist($targetDirectory.'/vendor/nesbot/carbon/extension.neon');

        unlink($tmpZip);
        $this->cleanTargetDirectory();
    }
}
