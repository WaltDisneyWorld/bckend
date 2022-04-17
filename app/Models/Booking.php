<?php

namespace App\Models;

use App\Models\Master\MaBookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Booking
 *
 * @property int $id
 * @property int $queue_calendar_setting_id
 * @property int $line_member_id
 * @property int $status
 * @property string $customer_name Customer Name
 * @property string $customer_contact Contact
 * @property string $booking_date Booking date/time
 * @property string|null $confirmed_date Booking confirmed Datetime
 * @property int|null $confirmed_by
 * @property string|null $reject_date Booking reject date time
 * @property int|null $reject_by
 * @property string|null $revise_date Booking Revise date time
 * @property int|null $revise_by
 * @property string|null $done_date Booking Done date time
 * @property int|null $done_by
 * @property string|null $cancel_date Customer Cancel Datetime
 * @property string|null $lost_date Customer Lost Datetime
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read MaBookingStatus|null $booking_status
 * @property-read \App\Models\QueueCalendarSetting $calendar_setting
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCancelDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereConfirmedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCustomerContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereDoneBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereDoneDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereLineMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereLostDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereQueueCalendarSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRejectBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRejectDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereReviseBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereReviseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Booking extends Model
{
    use HasFactory;

    protected $table = "booking";

    function booking_status()
    {
        return $this->hasOne(MaBookingStatus::class, 'status');
    }

    function calendar_setting()
    {
        return $this->belongsTo(QueueCalendarSetting::class, 'queue_calendar_setting_id');
    }
}
