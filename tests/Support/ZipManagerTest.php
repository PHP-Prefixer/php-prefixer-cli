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
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * @coversNothing
 */
final class ZipManagerTest extends TestCase
{
    public function testCompressUncompress()
    {
        $zipManager = new ZipManager();

        // Let's start fresh
        $targetDirectory = env('TARGET_DIRECTORY');
        File::cleanDirectory($targetDirectory);

        $sourceDirectory = env('SOURCE_DIRECTORY');
        $tmpZip = getcwd().'/test.zip';
        $status = $zipManager->compress($sourceDirectory, $tmpZip);

        $this->assertTrue($status);
        $this->assertFileExists($tmpZip);

        $zipManager->uncompress($tmpZip, $targetDirectory);

        $this->assertFileExists($targetDirectory.'/app');
        $this->assertFileExists($targetDirectory.'/composer.json');

        unlink($tmpZip);
        File::cleanDirectory($targetDirectory);
    }
}
