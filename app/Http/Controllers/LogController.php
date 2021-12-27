<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;


class LogController extends Controller
{
    public function __construct(public ActivityService $activityService)
    {
        //
    }

    public function getSignedActivity()
    {
        dd($this->activityService->getActivityLogs());
    }
}
