<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class ActivityService
{
    private const IDEA_BUY_ORDER = 'App\Containers\Idea\Models\IdeaBuyOrder';
    private const SIGNED = 'signed';
    private const STATUS_UPDATED = 'status_updated_at';
    private const STATUS = 'status';
    private const ATTRIBUTES = 'attributes';
    private const SUBJECT_ID = 'subject_id';
    private const SIGNET_AT = 'signed_at';

    /**
     * @return Collection
     */
    public function getActivityLogs(): Collection
    {
        $logs = Activity::where('subject_type', self::IDEA_BUY_ORDER)
            ->get();

        $result = [];
        $i = 0;

        $logs->each(function (Activity $activity) use (&$result, &$i) {
            foreach ($activity->properties as $value) {
                if (array_key_exists(self::STATUS, $value) && $value[self::STATUS] === self::SIGNED) {
                    $result[$i][self::SUBJECT_ID] = $activity->subject_id;
                    if (array_key_exists(self::STATUS_UPDATED, $value)) {
                        $result[$i][self::SIGNET_AT] = $value[self::STATUS_UPDATED];
                    } else {
                        $activities = Activity::query()
                            ->where(self::SUBJECT_ID, $activity->subject_id)
                            ->get();
                        $activities->map(function ($act) use (&$result, $i) {
                            if (array_key_exists(self::ATTRIBUTES, $act->properties->toArray())
                                &&
                                array_key_exists(self::STATUS_UPDATED, $act->properties[self::ATTRIBUTES])) {
                                $result[$i][self::SIGNET_AT] = $act->properties[self::ATTRIBUTES][self::STATUS_UPDATED];
                            };
                        });
                    }
                    $i++;
                }
            }

        });

        return collect($result)->unique('subject_id');
    }

}
