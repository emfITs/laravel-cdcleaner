<?php

namespace Emfits\CDCleaner;

use Emfits\CDCleaner\Exceptions\CouldNotReadLinkException;
use ErrorException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CDCleaner
{

    public function isRunningInCurrent(): bool
    {
        return Str::endsWith(haystack: App::basePath(), needles: config('cdcleaner.current', 'current'));
    }


    private ?string $web_path = null;
    public function getWebPath(): string
    {
        if (!$this->web_path) {
            $this->web_path = App::basePath() . Str::repeat('/..',  2) . '/';
        }
        return $this->web_path;
    }

    private ?string $release_path = null;
    public function getReleasePath(): string
    {
        if (!$this->release_path) {
            $this->release_path = realpath($this->getWebPath() . config('cdcleaner.releases', 'releases'));
        }
        return $this->release_path;
    }

    /**
     * @return string
     * @throws ErrorException
     */
    public function readCurrentLink(): string
    {
        $path = $this->getWebPath() . config('cdcleaner.current', 'current');
        try {
            $link = readlink($this->getWebPath() . config('cdcleaner.current', 'current'));
        } catch (Throwable $ex) {
            throw new CouldNotReadLinkException('Could not read link of ' . $path);
        }
        return str_replace($this->getReleasePath() . '/', '', $link);
    }

    public function isReleasePathADirectory(): bool
    {
        return is_dir($this->getReleasePath());
    }

    public function isWebPathADirectory(): bool
    {
        return is_dir($this->getWebPath());
    }

    public function getReleaseDirectories()
    {
        return scandir($this->getReleasePath());
    }

    public function getSuccessDirectories(): ?array
    {
        $path = config('cdcleaner.storage_path') . '/successPaths.json';
        if (!Storage::fileExists($path)) {
            return [];
        }
        return json_decode(Storage::get($path), true);
    }

    public function addActualDirectoryToSuccess(): bool
    {
        $arr = $this->getSuccessDirectories();
        $arr[] = $this->getActualReleasePath();
        $arr = array_unique($arr);
        $arr = array_splice($arr, 0, (-1 * config('cdcleaner.keep', 3)), null);
        $arr = array_filter($arr);
        return $this->saveSuccessDirectories($arr);
    }

    public function saveSuccessDirectories(array $arr): bool
    {
        $this->createStoragePathIfNotExists();
        $path = config('cdcleaner.storage_path') . '/successPaths.json';
        return Storage::put($path, json_encode(array_values($arr)));
    }

    public function createStoragePathIfNotExists()
    {
        if (Storage::directoryMissing(config('cdcleaner.storage_path'))) {
            Storage::makeDirectory(config('cdcleaner.storage_path'));
        }
    }

    public function removeCurrentFromDirectoryList(array &$arr)
    {
        if (($key = array_search(haystack: $arr, needle: $this->readCurrentLink())) !== false) {
            unset($arr[$key]);
        }
    }

    public function getActualReleasePath(): string|false
    {
        return $this->isRunningInCurrent() ? readlink(App::basePath()) : App::basePath();
    }

    public function removeActualDirectoryFromList(array &$arr)
    {
        $path = $this->getActualReleasePath();
        // @codeCoverageIgnoreStart
        if (($key = array_search(haystack: $arr, needle: $path)) !== FALSE) {
            unset($arr[$key]);
        }
        // @codeCoverageIgnoreEnd
    }

    public function removeUnwantedFolders(array &$arr)
    {
        $arr = array_filter($arr, fn($item) => $item != '.' && $item != '..');
    }

    public function filterByPattern(array &$arr)
    {
        $arr = array_filter($arr, fn($item) => preg_match('/^\d{14}$/', $item) != false);
    }

    /**
     * This will che
     */
    public function checkStructureAndPaths(): bool
    {
        return $this->isReleasePathADirectory() && $this->isWebPathADirectory();
    }

    public function run(): int
    {
        $scan = $this->getReleaseDirectories();
        $this->removeUnwantedFolders($scan);
        $this->filterByPattern($scan);
        $this->removeCurrentFromDirectoryList($scan);
        $this->removeActualDirectoryFromList($scan);
        if (!$this->hasMoreThanToKeep($scan)) {
            $this->addActualDirectoryToSuccessIfActive();
            return 0;
        }
        // $this->removePreviousLink($scan);
        $successes = $this->getSuccessDirectories();
        $last = array_shift($successes);
        $lastPath = substr($last, strrpos($last, '/'));
        if (config('cdcleaner.keep_failed', true)) {
            $scan = array_filter($scan, function ($item) use ($lastPath): bool {
                return Carbon::createFromFormat('YmdHis', $item) < Carbon::createFromFormat('YmdHis', $lastPath);
            });
        } else {
            $this->removeSuccessesFromList($scan);
        }

        $this->deleteDirectories($scan);

        $this->addActualDirectoryToSuccessIfActive();
        return count($scan);
    }


    public function removeSuccessesFromList(array &$arr)
    {
        $successes = $this->getSuccessDirectories();
        $successes = array_map(fn($item) => substr($item, strrpos($item, '/')), $successes);
        $arr = array_filter($arr, fn($item) => !in_array($item, $successes));
    }

    public function addActualDirectoryToSuccessIfActive()
    {
        if (!config('cdcleaner.add_actual_path_after_run', true)) {
            return;
        }
        $this->addActualDirectoryToSuccess();
    }

    public function hasMoreThanToKeep(array $found = [])
    {
        if (config('cdcleaner.keep_failed', true)) {
            return count($this->getSuccessDirectories()) >= config('cdcleaner.keep', 3);
        }
        return count($found) >= config('cdcleaner.keep', 3);
    }

    public function deleteDirectories(array $scan)
    {
        $paths = array_map(fn($item) => realpath($this->getReleasePath() . '/' . $item), $scan);
        foreach ($paths as $path) {
            if ($path == '/' || $path == '\\' || !str_starts_with($path, $this->getReleasePath())) {
                continue;
            }
            // @codeCoverageIgnoreStart
            if (PHP_OS === 'Windows') {
                exec(sprintf("rd /s /q %s", escapeshellarg($path)));
            } else {
                exec(sprintf("rm -rf %s", escapeshellarg($path)));
            }
            // @codeCoverageIgnoreEnd
        }
    }
}
