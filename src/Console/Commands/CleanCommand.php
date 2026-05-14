<?php

namespace Emfits\CDCleaner\Console\Commands;

use Carbon\Carbon;
use Emfits\CDCleaner\CDCleaner;
use Emfits\CDCleaner\Exceptions\CouldNotReadLinkException;
use ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Throwable;

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
    public function handle(CDCleaner $cdCleaner): ?int
    {
        try {
            if (!$cdCleaner->checkStructureAndPaths()) {
                $this->output->error('There is no web documentroot or a given release_base. Please provide one within your config or with your .env file.');
                return -1;
            }
            $counter = $cdCleaner->run();
            $this->output->success('Deleted all old release directories. Number of deleted directories: ' . $counter);
        } catch (Throwable $ex) {
            if ($ex instanceof CouldNotReadLinkException) {
                $this->output->error("Error while trying to read the link of your configured current directory.");
                return -2;
            } else {
                $this->output->error($ex->getMessage() . "\n" . $ex->getTraceAsString());
                return -3;
            }
        }


        return 0;
        //  on readlinke
        // $this->output->error("Error while trying to read the link of your configured current directory.");
        // if release dir and web path are not existing
        //Checks if the command was executet inside the current directory or in the release directory
    }
}
