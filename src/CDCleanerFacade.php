<?php

namespace Emfits\CDCleaner;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Emfits\CDCleaner\Skeleton\SkeletonClass
 */
class CDCleanerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cdcleaner';
    }
}
