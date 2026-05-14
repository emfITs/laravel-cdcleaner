<?php

namespace Emfits\CDCleaner\Tests;

use Emfits\CDCleaner\CDCleanerServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            CDCleanerServiceProvider::class,
        ];
    }

    protected ?string $tmpPath = null;
    protected ?string $basePath = null;
    protected ?string $releasePath = null;
    protected ?string $currentPath = null;
    protected function getEnvironmentSetUp($app)
    {
        $this->initBasePath();
    }

    protected function initBasePath()
    {
        if ($this->basePath) {
            return;
        }
        $this->tmpPath = __DIR__ . '/tmp';
        $this->basePath = $this->tmpPath . '/releases/' . Carbon::now()->format('YmdHis');
        $this->releasePath = $this->basePath;
        $this->currentPath = $this->tmpPath . '/current';
        mkdir($this->basePath, recursive: true);
        mkdir($this->basePath . '/bootstrap/cache', recursive: true);
        if (is_dir($this->currentPath) || is_link($this->currentPath)) {
            unlink($this->currentPath);
        }
        symlink($this->releasePath, $this->currentPath);
    }

    protected function setCurrentAsBasePath()
    {
        $this->setReleaseAsBasePath();
        $this->basePath = $this->currentPath;
        if (is_dir($this->currentPath) || is_link($this->currentPath)) {
            unlink($this->currentPath);
        }
        symlink($this->releasePath, $this->currentPath);
        App::setBasePath($this->basePath);
    }

    protected function setReleaseAsBasePath()
    {
        $this->basePath = $this->releasePath;
        if (!file_exists($this->basePath)) {
            mkdir($this->basePath, recursive: true);
        }
        if (is_dir($this->currentPath) || is_link($this->currentPath)) {
            unlink($this->currentPath);
        }
        if (!file_exists($this->currentPath)) {
            symlink($this->releasePath, $this->currentPath);
        }
        if (!file_exists($this->basePath . '/bootstrap')) {
            mkdir($this->basePath . '/bootstrap/cache', recursive: true);
        }
        App::setBasePath($this->basePath);
    }

    protected function setTestStructureAsBasePath()
    {
        $this->basePath = $this->tmpPath;
        if (!file_exists($this->basePath . '/bootstrap')) {
            mkdir($this->basePath . '/bootstrap/cache', recursive: true);
        }
        App::setBasePath($this->basePath);
    }

    protected function getBasePath()
    {
        $this->initBasePath();
        // Beispiel: Zeige auf den 'workbench' Ordner oder ein lokales Verzeichnis
        return $this->basePath;
    }

    protected function createFakeReleases(array $releaseNames)
    {
        $path = __DIR__ . '/tmp/releases';
        File::makeDirectory($path, 0755, true, true);

        foreach ($releaseNames as $name) {
            File::makeDirectory($path . '/' . $name);
            // Optional: Dummy-Datei erstellen, um "Inhalt" zu simulieren
            File::put($path . '/' . $name . '/version.txt', $name);
        }
    }

    protected function tearDown(): void
    {
        // Nach jedem Test den tmp-Ordner löschen
        File::deleteDirectory(__DIR__ . '/tmp');
        parent::tearDown();
    }
}
