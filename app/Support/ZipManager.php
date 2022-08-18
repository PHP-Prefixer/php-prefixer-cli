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

use PhpZip\Exception\InvalidArgumentException;
use PhpZip\Util\FilesUtil;
use PhpZip\ZipFile;

class ZipManager
{
    // Patterns to produce clean packages
    public const HIDDEN_FILES_PATTERN = '~(\/(\.[^/]+))|(^\.[^/]+)~';
    public const VENDOR_PATTERN = '~(^vendor\/|\/vendor\/)~';
    public const NODE_MODULES_PATTERN = '~(^node_modules\/|\/node_modules\/)~';

    private $includeVendor = false;
    private $includeAll = false;

    public function includeVendor($include = false)
    {
        $this->includeVendor = $include;

        return $this;
    }

    public function includeAll($include = false)
    {
        $this->includeAll = $include;

        return $this;
    }

    public function compress($projectPath, $packageZipPath)
    {
        $zipFile = null;

        try {
            $zipFile = $this->addDirRecursiveToZipFile($projectPath);

            // Delete all hidden (Unix) files
            if ( ! $this->includeAll ) {
                $zipFile->deleteFromRegex(self::HIDDEN_FILES_PATTERN);
            }

            // Exclude other vendors
            if ( ! $this->includeAll ) {
                $zipFile->deleteFromRegex(self::NODE_MODULES_PATTERN);
            }

            // Delete vendor (ignore the Customized vendor case)
            if ( ! $this->includeVendor && ! $this->includeAll ) {
                $zipFile->deleteFromRegex(self::VENDOR_PATTERN);
            }

            $zipFile->saveAsFile($packageZipPath)
                ->close();

            return true;
        } finally {
            if ($zipFile) {
                $zipFile->close();
            }
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

    private function addDirRecursiveToZipFile(string $projectPath): ZipFile
    {
        $zipFile = new ZipFile();

        //$zipFile->addDirRecursive($projectPath)
        return $this->addDirRecursive($zipFile, $projectPath);
    }

    private function addDirRecursive(ZipFile $zipFile, string $inputDir, string $localPath = '/', int $compressionMethod = null): ZipFile
    {
        if ('' === $inputDir) {
            throw new InvalidArgumentException('The input directory is not specified');
        }

        if (!is_dir($inputDir)) {
            throw new InvalidArgumentException(sprintf('The "%s" directory does not exist.', $inputDir));
        }
        $inputDir = rtrim($inputDir, '/\\').\DIRECTORY_SEPARATOR;

        $directoryIterator = new \RecursiveDirectoryIterator($inputDir);

        return $this->addFilesFromIterator($zipFile, $directoryIterator, $localPath, $compressionMethod);
    }

    private function addFilesFromIterator(
        ZipFile $zipFile,
        \Iterator $iterator,
        string $localPath = '/',
        int $compressionMethod = null
    ): ZipFile {
        if ('' !== $localPath) {
            $localPath = trim($localPath, '\\/');
        } else {
            $localPath = '';
        }

        $iterator = $iterator instanceof \RecursiveIterator
            ? new \RecursiveIteratorIterator($iterator)
            : new \IteratorIterator($iterator);
        /**
         * @var string[] $files
         * @var string   $path
         */
        $files = [];

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo) {
                if ('..' === $file->getBasename()) {
                    continue;
                }

                if ('.' === $file->getBasename()) {
                    $files[] = \dirname($file->getPathname());

                    continue;
                }

                $extension = $file->getExtension();
                $filename = $file->getFilename();

                if ( ! $this->includeAll && 'php' !== $extension
                    && 'composer.json' !== $filename && 'composer.lock' !== $filename) {
                    continue;
                }

                $pathname = $file->getPathname();
                $files[] = $pathname;
            }
        }

        if (empty($files)) {
            return $this;
        }

        natcasesort($files);
        $path = array_shift($files);

        $this->doAddFiles($zipFile, $path, $files, $localPath, $compressionMethod);

        return $zipFile;
    }

    private function doAddFiles(
        ZipFile $zipFile,
        string $fileSystemDir,
        array $files,
        string $zipPath,
        int $compressionMethod = null
    ): void {
        $fileSystemDir = rtrim($fileSystemDir, '/\\').\DIRECTORY_SEPARATOR;

        if (!empty($zipPath)) {
            $zipPath = trim($zipPath, '\\/').'/';
        } else {
            $zipPath = '/';
        }

        /**
         * @var string $file
         */
        foreach ($files as $file) {
            $filename = str_replace($fileSystemDir, $zipPath, $file);
            $filename = ltrim($filename, '\\/');

            if (is_dir($file) && FilesUtil::isEmptyDir($file)) {
                $zipFile->addEmptyDir($filename);
            } elseif (is_file($file)) {
                $zipFile->addFile($file, $filename, $compressionMethod);
            }
        }
    }
}
