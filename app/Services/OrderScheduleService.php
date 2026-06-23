<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class OrderScheduleService
{
    /**
     * @var array<int, array{code:int,key:string,label:string,start:string,end:string}>
     */
    private const SLOTS = [
        1 => ['code' => 1, 'key' => 'morning', 'label' => 'Pagi', 'start' => '07:00', 'end' => '10:00'],
        2 => ['code' => 2, 'key' => 'afternoon', 'label' => 'Siang', 'start' => '11:00', 'end' => '14:00'],
        3 => ['code' => 3, 'key' => 'evening', 'label' => 'Sore', 'start' => '15:00', 'end' => '17:00'],
    ];

    /**
     * Resolve selected delivery schedule from request inputs.
     *
     * @return array{
     *     date:string,
     *     slot_code:int,
     *     slot_key:string,
     *     slot_label:string,
     *     window_start:string,
     *     window_end:string,
     *     schedule_at:string,
     *     is_auto_assigned:bool,
     *     auto_shifted_to_next_day:bool,
     *     timezone:string
     * }
     */
    public function resolve(
        ?string $scheduleDate = null,
        ?int $scheduleSlotCode = null,
        ?string $scheduleAt = null,
        ?CarbonInterface $now = null,
    ): array {
        $now = $this->normalizeNow($now);
        $timezone = $this->timezone();
        $isAutoAssigned = false;
        $autoShifted = false;

        if (is_string($scheduleAt) && trim($scheduleAt) !== '') {
            $candidate = CarbonImmutable::parse($scheduleAt, $timezone);
            $date = $candidate->startOfDay();
            [$slotCode, $slotDate, $autoShifted] = $this->resolveSlotByTimeCandidate($candidate, $date, $now);
        } elseif (($scheduleDate !== null && $scheduleDate !== '') || $scheduleSlotCode !== null) {
            $slotCode = $scheduleSlotCode ?? $this->nextAvailableSlotCode($now);
            $slot = $this->slot($slotCode);

            if ($scheduleDate !== null && $scheduleDate !== '') {
                $date = CarbonImmutable::createFromFormat('Y-m-d', $scheduleDate, $timezone)->startOfDay();
            } else {
                $date = $now->startOfDay();
            }

            $slotDate = $date;
            if ($slotDate->isSameDay($now)) {
                $slotEnd = $slotDate->setTimeFromTimeString($slot['end']);
                if ($now->greaterThanOrEqualTo($slotEnd)) {
                    $slotDate = $slotDate->addDay();
                    $autoShifted = true;
                }
            }
        } else {
            [$slotCode, $slotDate] = $this->nextAvailable($now);
            $isAutoAssigned = true;
        }

        $slot = $this->slot($slotCode);
        $scheduledAt = $slotDate->setTimeFromTimeString($slot['start']);

        return [
            'date' => $slotDate->format('Y-m-d'),
            'slot_code' => $slotCode,
            'slot_key' => $slot['key'],
            'slot_label' => $slot['label'],
            'window_start' => $slot['start'],
            'window_end' => $slot['end'],
            'schedule_at' => $scheduledAt->toISOString(),
            'is_auto_assigned' => $isAutoAssigned,
            'auto_shifted_to_next_day' => $autoShifted,
            'timezone' => $timezone,
        ];
    }

    /**
     * @return array{
     *     timezone:string,
     *     selected:array{
     *         date:string,
     *         slot_code:int,
     *         slot_key:string,
     *         slot_label:string,
     *         window_start:string,
     *         window_end:string,
     *         schedule_at:string,
     *         is_auto_assigned:bool,
     *         auto_shifted_to_next_day:bool
     *     },
     *     options:array<int,array{slot_code:int,slot_key:string,slot_label:string,window_start:string,window_end:string}>,
     *     calendar:array<int,array{
     *         date:string,
     *         is_today:bool,
     *         slots:array<int,array{
     *             slot_code:int,
     *             slot_key:string,
     *             slot_label:string,
     *             window_start:string,
     *             window_end:string,
     *             schedule_at:string,
     *             is_available:bool
     *         }>
     *     }>
     * }
     */
    public function buildMetadata(array $selected, ?CarbonInterface $now = null): array
    {
        $now = $this->normalizeNow($now);
        $timezone = $this->timezone();
        $today = $now->startOfDay();

        $options = [];
        foreach (self::SLOTS as $slot) {
            $options[] = [
                'slot_code' => $slot['code'],
                'slot_key' => $slot['key'],
                'slot_label' => $slot['label'],
                'window_start' => $slot['start'],
                'window_end' => $slot['end'],
            ];
        }

        $calendar = [];
        foreach ([0, 1] as $offset) {
            $date = $today->addDays($offset);
            $daySlots = [];

            foreach (self::SLOTS as $slot) {
                $slotEnd = $date->setTimeFromTimeString($slot['end']);
                $slotStart = $date->setTimeFromTimeString($slot['start']);

                $daySlots[] = [
                    'slot_code' => $slot['code'],
                    'slot_key' => $slot['key'],
                    'slot_label' => $slot['label'],
                    'window_start' => $slot['start'],
                    'window_end' => $slot['end'],
                    'schedule_at' => $slotStart->toISOString(),
                    'is_available' => ! $date->isSameDay($now) || $now->lessThan($slotEnd),
                ];
            }

            $calendar[] = [
                'date' => $date->format('Y-m-d'),
                'is_today' => $offset === 0,
                'slots' => $daySlots,
            ];
        }

        return [
            'timezone' => $timezone,
            'selected' => [
                'date' => $selected['date'],
                'slot_code' => $selected['slot_code'],
                'slot_key' => $selected['slot_key'],
                'slot_label' => $selected['slot_label'],
                'window_start' => $selected['window_start'],
                'window_end' => $selected['window_end'],
                'schedule_at' => $selected['schedule_at'],
                'is_auto_assigned' => (bool) $selected['is_auto_assigned'],
                'auto_shifted_to_next_day' => (bool) $selected['auto_shifted_to_next_day'],
            ],
            'options' => $options,
            'calendar' => $calendar,
        ];
    }

    /**
     * Describe order schedule from stored schedule_at.
     *
     * @return array{
     *     date:string,
     *     slot_code:int,
     *     slot_key:string,
     *     slot_label:string,
     *     window_start:string,
     *     window_end:string,
     *     schedule_at:string,
     *     timezone:string
     * }|null
     */
    public function describe(?CarbonInterface $scheduleAt): ?array
    {
        if (! $scheduleAt) {
            return null;
        }

        $schedule = CarbonImmutable::instance($scheduleAt)->setTimezone($this->timezone());
        [$slotCode, $slotDate] = $this->resolveSlotFromDateAndTime($schedule, $schedule->startOfDay());
        $slot = $this->slot($slotCode);

        return [
            'date' => $slotDate->format('Y-m-d'),
            'slot_code' => $slotCode,
            'slot_key' => $slot['key'],
            'slot_label' => $slot['label'],
            'window_start' => $slot['start'],
            'window_end' => $slot['end'],
            'schedule_at' => $slotDate->setTimeFromTimeString($slot['start'])->toISOString(),
            'timezone' => $this->timezone(),
        ];
    }

    private function timezone(): string
    {
        return (string) config('api.schedule.timezone', 'Asia/Jakarta');
    }

    private function normalizeNow(?CarbonInterface $now): CarbonImmutable
    {
        if (! $now) {
            return CarbonImmutable::now($this->timezone());
        }

        return CarbonImmutable::instance($now)->setTimezone($this->timezone());
    }

    /**
     * @return array{code:int,key:string,label:string,start:string,end:string}
     */
    private function slot(int $slotCode): array
    {
        return self::SLOTS[$slotCode] ?? self::SLOTS[1];
    }

    /**
     * @return array{0:int,1:CarbonImmutable}
     */
    private function nextAvailable(CarbonImmutable $now): array
    {
        $today = $now->startOfDay();

        foreach (self::SLOTS as $slot) {
            $slotEnd = $today->setTimeFromTimeString($slot['end']);
            if ($now->lessThan($slotEnd)) {
                return [$slot['code'], $today];
            }
        }

        return [1, $today->addDay()];
    }

    private function nextAvailableSlotCode(CarbonImmutable $now): int
    {
        [$slotCode] = $this->nextAvailable($now);

        return $slotCode;
    }

    /**
     * @return array{0:int,1:CarbonImmutable,2:bool}
     */
    private function resolveSlotByTimeCandidate(
        CarbonImmutable $candidate,
        CarbonImmutable $date,
        CarbonImmutable $now,
    ): array {
        [$slotCode, $slotDate] = $this->resolveSlotFromDateAndTime($candidate, $date);
        $slot = $this->slot($slotCode);
        $autoShifted = false;

        if ($slotDate->isSameDay($now)) {
            $slotEnd = $slotDate->setTimeFromTimeString($slot['end']);
            if ($now->greaterThanOrEqualTo($slotEnd)) {
                $slotDate = $slotDate->addDay();
                $autoShifted = true;
            }
        }

        return [$slotCode, $slotDate, $autoShifted];
    }

    /**
     * @return array{0:int,1:CarbonImmutable}
     */
    private function resolveSlotFromDateAndTime(CarbonImmutable $dateTime, CarbonImmutable $date): array
    {
        $hourMinute = ((int) $dateTime->format('H')) * 60 + (int) $dateTime->format('i');
        $slotCode = 1;
        $slotDate = $date;

        if ($hourMinute < 10 * 60) {
            $slotCode = 1;
        } elseif ($hourMinute < 15 * 60) {
            $slotCode = 2;
        } elseif ($hourMinute < 17 * 60) {
            $slotCode = 3;
        } else {
            $slotCode = 1;
            $slotDate = $slotDate->addDay();
        }

        return [$slotCode, $slotDate];
    }
}
