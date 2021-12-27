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
    private const SUBJECT_TYPE = 'subject_type';

    /**
     * @return Collection
     */
    public function getActivityLogs(): Collection
    {
        $logs = Activity::query()->where(self::SUBJECT_TYPE, self::IDEA_BUY_ORDER)
            ->get();
        $signedLogs = $logs->where('properties.attributes.status', self::SIGNED);
        $result = collect();

        $signedLogs->each(function (Activity $activity) use (&$result, $logs) {
            if (isset($activity->properties[self::ATTRIBUTES][self::STATUS_UPDATED])) {
                $result->add([
                    self::SUBJECT_ID => $activity->subject_id,
                    self::SIGNET_AT => $activity->properties[self::ATTRIBUTES][self::STATUS_UPDATED]
                ]);
                return;
            }

            $previousLogs = $logs->where(self::SUBJECT_ID, $activity->subject_id)
                ->where(self::SUBJECT_TYPE, self::IDEA_BUY_ORDER);

            /** @var ?Activity $sentLog */
            $sentLog = $previousLogs->firstWhere('properties.attributes.status', 'sent');

            if (isset($sentLog->properties[self::ATTRIBUTES][self::STATUS_UPDATED])) {
                $result->add([
                    self::SUBJECT_ID => $activity->subject_id,
                    self::SIGNET_AT => $activity->properties[self::ATTRIBUTES][self::STATUS_UPDATED]
                ]);
                return;
            }

            /** @var ?Activity $createdLog */
            $createdLog = $previousLogs->firstWhere('description', 'created');

            if (isset($createdLog->properties[self::ATTRIBUTES][self::STATUS_UPDATED])) {
                $result->add([
                    self::SUBJECT_ID => $createdLog->subject_id,
                    self::SIGNET_AT => $createdLog->properties[self::ATTRIBUTES][self::STATUS_UPDATED]
                ]);
            }
        });

        return $result;
    }

}
