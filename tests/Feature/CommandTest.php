<?php

use Emfits\CDCleaner\CDCleaner;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('can run the cleanup command without removing anything due to empty success', function () {
    $cls = new CDCleaner();
    $cls->saveSuccessDirectories([
        //Y  m d H i s
        '20260102030405'
    ]);
    // Storage::put('cdcleaner/successPaths.json', json_encode(['20260101200105', '20260310102030']));
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);
});

it('can run the cleanup command with release path', function () {
    $cls = new CDCleaner();
    $success = [
        '20260301030405',
        '20260401030405',
        '20260501030405',
    ];
    $cls->saveSuccessDirectories($success);
    foreach ($success as $path) {
        mkdir($cls->getReleasePath() . '/' . $path);
    }
    $shouldBeDeleted = [
        $cls->getReleasePath() . '/20260202030405',
        $cls->getReleasePath() . '/20260203030405',
        $cls->getReleasePath() . '/20260204030405',
        $cls->getReleasePath() . '/20260205030405',
    ];
    foreach ($shouldBeDeleted as $path) {
        mkdir($path);
    }
    // Storage::makeDirectory('cdcleaner');
    // Storage::put('cdcleaner/successPaths.json', json_encode(['20260101200105', '20260310102030']));
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);

    foreach ($success as $path) {
        expect(file_exists($cls->getReleasePath() . '/' . $path))->toBeTrue();
    }
    foreach ($shouldBeDeleted as $path) {
        expect(file_exists($path))->toBeFalse();
    }

    foreach ($success as $path) {
        rmdir($cls->getReleasePath() . '/' . $path);
    }
});

it('can run the cleanup command with release path and keep failed', function () {
    $cls = new CDCleaner();
    $success = [
        '20260201030405',
        '20260301030405',
        '20260401030405',
    ];
    $cls->saveSuccessDirectories($success);
    foreach ($success as $path) {
        mkdir($cls->getReleasePath() . '/' . $path);
    }
    $shouldBeDeleted = [
        $cls->getReleasePath() . '/20260202030405',
        $cls->getReleasePath() . '/20260203030405',
        $cls->getReleasePath() . '/20260204030405',
        $cls->getReleasePath() . '/20260205030405',
    ];
    foreach ($shouldBeDeleted as $path) {
        mkdir($path);
    }
    // Storage::makeDirectory('cdcleaner');
    // Storage::put('cdcleaner/successPaths.json', json_encode(['20260101200105', '20260310102030']));
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);

    foreach ($success as $path) {
        expect(file_exists($cls->getReleasePath() . '/' . $path))->toBeTrue();
    }
    foreach ($shouldBeDeleted as $path) {
        expect(file_exists($path))->toBeTrue();
    }
    foreach ($shouldBeDeleted as $path) {
        rmdir($path);
    }
    foreach ($success as $path) {
        rmdir($cls->getReleasePath() . '/' . $path);
    }
});

it('can run the cleanup command with current path', function () {
    $this->setCurrentAsBasePath();
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);
});

it('the structure is not found.', function () {
    $this->setTestStructureAsBasePath();
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(-1);
});

it('the current folder was not found.', function () {
    $this->setReleaseAsBasePath();
    unlink(App::basePath() . '/../../current');
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(-2);
});

it('throws an exception', function () {
    $cls = new CDCleaner();
    $cls->saveSuccessDirectories([]);
    Storage::put(config('cdcleaner.storage_path') . '/successPaths.json', json_encode("test"));
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(-3);
});

it('do not keep failed', function () {
    $cls = new CDCleaner();
    $success = [
        '20260201030405',
        '20260301030405',
        '20260401030405',
    ];
    $cls->saveSuccessDirectories($success);
    foreach ($success as $path) {
        mkdir($cls->getReleasePath() . '/' . $path);
    }
    $shouldBeDeleted = [
        $cls->getReleasePath() . '/20260202030405',
        $cls->getReleasePath() . '/20260203030405',
        $cls->getReleasePath() . '/20260204030405',
        $cls->getReleasePath() . '/20260205030405',
    ];
    foreach ($shouldBeDeleted as $path) {
        mkdir($path);
    }
    Config::set('cdcleaner.keep_failed', false);
    // Storage::makeDirectory('cdcleaner');
    // Storage::put('cdcleaner/successPaths.json', json_encode(['20260101200105', '20260310102030']));
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);

    foreach ($success as $path) {
        expect(file_exists($cls->getReleasePath() . '/' . $path))->toBeTrue();
    }

    foreach ($shouldBeDeleted as $path) {
        expect(file_exists($path))->toBeFalse();
    }

    foreach ($success as $path) {
        rmdir($cls->getReleasePath() . '/' . $path);
    }

    Config::set('cdcleaner.keep_failed', true);
});

it('should not add the latest entry', function () {
    $this->setReleaseAsBasePath();
    $cls = new CDCleaner();
    $cls->saveSuccessDirectories([]);
    Config::set('cdcleaner.add_actual_path_after_run', false);
    artisan('emfits:cdcleaner:clean')
        ->assertExitCode(0);

    expect($cls->getSuccessDirectories())->toBe([]);

    Config::set('cdcleaner.add_actual_path_after_run', true);
});


it('it should not delete outside of the release path', function () {
    $this->setReleaseAsBasePath();
    $cls = new CDCleaner();
    $cls->saveSuccessDirectories([]);
    expect(fn() => $cls->deleteDirectories(['../../../']))->not()->toThrow(Throwable::class);
});
