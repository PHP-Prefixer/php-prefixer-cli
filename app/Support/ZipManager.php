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

use PhpZip\ZipFile;

class ZipManager
{
    // Patterns to produce clean packages
    const HIDDEN_FILES_PATTERN = '~(\/(\.[^/]+))|(^\.[^/]+)~';
    const VENDOR_PATTERN = '~(^vendor\/|\/vendor\/)~';
    const NODE_MODULES_PATTERN = '~(^node_modules\/|\/node_modules\/)~';

    private $excludeVendor = true;

    public function excludeVendor($exclude = true)
    {
        $this->excludeVendor = $exclude;

        return $this;
    }

    public function compress($projectPath, $packageZipPath)
    {
        $zipFile = new ZipFile();

        try {
            $zipFile->addDirRecursive($projectPath)

                // Delete all hidden (Unix) files
                ->deleteFromRegex(self::HIDDEN_FILES_PATTERN)

                // Exclude other vendors
                ->deleteFromRegex(self::NODE_MODULES_PATTERN);

            // Delete vendor (ignore the Customized vendor case)
            if ($this->excludeVendor) {
                $zipFile->deleteFromRegex(self::VENDOR_PATTERN);
            }

            $zipFile->saveAsFile($packageZipPath)
                ->close();

            return true;
        } finally {
            $zipFile->close();
        }

        return false;
    }

    public function uncompress($file, $path)
    {
        $zipFile = new ZipFile();

        try {
            $zipFile
                ->openFile($file)
                ->extractTo($path);

            return true;
        } finally {
            $zipFile->close();
        }

        return false;
    }
}
