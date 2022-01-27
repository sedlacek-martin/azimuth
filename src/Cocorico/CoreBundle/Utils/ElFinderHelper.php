<?php

namespace Cocorico\CoreBundle\Utils;

use Symfony\Component\Filesystem\Filesystem;

class ElFinderHelper
{
    const GLOBAL_DIR = 'global';

    public static function getOrCreateFolder(string $folder, string $rootDir): string
    {
        $path = "{$rootDir}/web/uploads/ckeditor/{$folder}";

        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            $fs->mkdir($path);
        }

        return $folder;
    }
}
