<?php

namespace Hyqo\Wire\Loaders;

use Hyqo\Cache\CacheLayerInterface;
use Hyqo\Cache\Layer\FilesystemLayer;
use Hyqo\Wire\Compiler;

class WireLoader implements \Latte\Loader
{
    /** @var string|null */
    protected $baseDir;

    /** @var CacheLayerInterface */
    protected $cache;

    public function __construct(string $baseDir, string $cacheDir)
    {
        $this->baseDir = (string)realpath($baseDir);
        $this->cache = new FilesystemLayer('wire', 0, $cacheDir);
    }

    public function getContent($name): string
    {
        $file = $this->generateFile($name);

        if (!is_file($file)) {
            throw new \RuntimeException("Missing template file '$file'.");
        }

        $timestamp = (int)filemtime($file);
//        $timestamp = 9999999999;

        $cacheItem = $this->cache->getItemCreatedAfter($timestamp, $file, static function () use ($file) {
            return Compiler::preprocess(file_get_contents($file));
        });

        if ($cacheItem->isHit()) {
//            echo "hit: $file\n";
        } else {
//            echo "cached: $file\n";
        }

        return $cacheItem;
    }

    public function isExpired($name, $time): bool
    {
        $mtime = (int)@filemtime($this->generateFile($name));

        if (!$mtime || $mtime > $time) {
//            echo "n expired: $name (file: $mtime, current $time)\n";
        } else {
//            echo "n hit: $name (file: $mtime, current $time)\n";
        }

        return !$mtime || $mtime > $time;
    }

    public function getReferredName($name, $referringName)
    {
        if (strpos($name, DIRECTORY_SEPARATOR) === 0) {
            $referringDir = $this->baseDir;
        } else {
            $referringDir = pathinfo($this->generateFile($referringName), PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
        }

        $templateDir = pathinfo($referringDir . $name, PATHINFO_DIRNAME);

        if (!is_dir($templateDir)) {
//            echo "fix: $name for $templateDir\n";
            return $name;
        }

        $templateDir = realpath($templateDir);
        $templateName = pathinfo($referringDir . $name, PATHINFO_FILENAME);

        $result = $templateDir . DIRECTORY_SEPARATOR . $templateName;

        return str_replace($this->baseDir . DIRECTORY_SEPARATOR, '', $result);
    }

    public function getUniqueId($name): string
    {
        return $this->generateFile($name);
    }

    protected function generateFile(string $name): string
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . $name . '.latte';
    }
}
