<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\CarClass;
use App\Enums\CarStatus;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckBookingTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-booking-timers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bookings = Booking::where('status', BookingStatus::Booked->value)->get();
        $timeNow = Carbon::now('UTC');
        foreach ($bookings as $booking) {
            if ($booking->booked_until < $timeNow) {
                $booking->status = BookingStatus::BookingTimeOver->value;
                $booking->car->status = CarStatus::AvailableForBooking->value;
                $booking->car->save();
                $booking->save();
            }
        }
    }
}
