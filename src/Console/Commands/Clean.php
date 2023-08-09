<?php

namespace Emfits\CDCleaner\Console\Commands;

use Illuminate\Console\Command;

class Clean extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdcleaner:clean';

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
        $current = $this->argument('current');
        $commandFile = __DIR__;
        // web/current/App/Console/Commands/
        if (strpos($commandFile, 'current') !== false) {
            $neededDir = $commandFile . '/../../../../../releases';
        } else {
            $neededDir = $commandFile . '/../../../../../../releases';
        }
        if (is_dir($neededDir)) {
            $scan = scandir($neededDir);
            unset($scan[0], $scan[1]);
            sort($scan);
            $prev_key = array_search(haystack: $scan, needle: $current);
            $prev_key = ($prev_key && count($scan) > 1) ? $prev_key - 1 : $prev_key;
            foreach ($scan as $key => $item) {
                if ($current == $item || $key == $prev_key) {
                    continue;
                }
                if (preg_match(pattern: '/^\d{14}$/', subject: $item)) {
                    exec('rm -r -f ' . $neededDir . '/' . $item);
                }
            }
        }
        $this->output->success('Deleted all old release directories.');

        return;
    }
}
