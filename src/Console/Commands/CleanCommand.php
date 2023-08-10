<?php

namespace Emfits\CDCleaner\Console\Commands;

use Carbon\Carbon;
use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emfits:cdcleaner:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes old releases directories.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {

        $base = base_path();
        //Checks if the command was executet inside the current directory or in the release directory
        $in_current = str_ends_with(haystack: $base, needle: config('cdcleaner.current', 'current'));
        // web/current/App/Console/Commands/
        $web_base = $base . (($in_current) ? '/..' : '/../..') . '/';
        $release_base = realpath($web_base . config('cdcleaner.releases', 'releases'));
        try {
            $current_link = str_replace($release_base . '/', '', readlink($web_base . config('cdcleaner.current', 'current')));
        }
        catch(ErrorException) {
            $this->output->error("Error while trying to read the link of your configured current directory.");
            return;
        }

        $prev_link = Cache::rememberForever('cdcleaner_last_release_dir', function () {
            return null;
        });

        if (! is_dir($release_base) || ! is_dir($web_base)) {
            $this->output->error('There is no web documentroot or a given release_base. Please provide one within your config or with your .env file.');

            return;
        }

        $scan = scandir($release_base);
        unset($scan[0], $scan[1]);
        if (($key = array_search(haystack: $scan, needle: $current_link)) !== false) {
            unset($scan[$key]);
        }
        if ($prev_link && ($key = array_search(haystack: $scan, needle: $prev_link)) !== false) {
            unset($scan[$prev_link]);
        }
        sort($scan);
        if (! config('cdcleaner.keep_failed', true) && $prev_link) {
            $not_working = array_filter($scan, function ($value) use ($prev_link) {
                return Carbon::createFromFormat('YmdHis', $value) > Carbon::createFromFormat('YmdHis', $prev_link);
            });
            $scan = array_diff($scan, $not_working);
        }
        // - 1 for the last working version
        $keep = config('cdcleaner.keep', 2) - (($prev_link) ? 1 : 0);
        $scan = array_splice($scan, 0, (-1 * $keep), null);
        $counter = 0;
        foreach ($scan as $key => $item) {
            if (is_dir($release_base . '/' . $item) && preg_match(pattern: '/^\d{14}$/', subject: $item)) {
                exec('rm -r -f ' . $release_base . '/' . $item);
                $counter++;
            }
        }

        Cache::forever('cdcleaner_last_release_dir', $current_link);

        $this->output->success('Deleted all old release directories. Number of deleted directories: ' . $counter);

    }
}
