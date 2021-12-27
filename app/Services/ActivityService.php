<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityService
{
    private const IDEA_BUY_ORDER = 'App\Containers\Idea\Models\IdeaBuyOrder';
    private const SIGNED = 'signed';
    private const STATUS_UPDATED = 'status_updated_at';

        /**
         * @return Collection
         */
        public function getActivityLogs(): Collection {
        $logs = Activity::where('subject_type', self::IDEA_BUY_ORDER)
            ->get();

        $result = [];
        $i = 0;

        $logs->each(function ($activity) use (&$result, &$i) {
            foreach (json_decode($activity->properties, true) as $value) {
                if (isset($value['status'])) {
                    if ($value['status'] === self::SIGNED) {
                        $result[$i]['subject_id'] = $activity->subject_id;

                        if (isset($value[self::STATUS_UPDATED])) {
                            $result[$i]['signed_at'] = $value[self::STATUS_UPDATED];
                        } else {
                            $activities = Activity::query()
                                ->where('subject_id', $activity->subject_id)
                                ->get();

                            $activities->map(function ($act) use (&$result, $i) {
                                $decode = json_decode($act->properties, true);
                                if (isset($decode['attributes'])) {
                                    if (isset($decode['attributes'][self::STATUS_UPDATED])) {
                                        $result[$i]['signed_at'] = $decode['attributes'][self::STATUS_UPDATED];
                                    }
                                };
                            });
                        }
                        $i++;
                    }
                }
            }
        });

        return collect($result)->unique('subject_id');
    }

}
