<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Center;
use App\Models\Crop;
use App\Models\Notice;
use App\Models\Procurement;
use App\Models\User;
use App\Services\SlotService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $img = fn (string $k, int $n) => "https://loremflickr.com/800/520/{$k}?lock={$n}";

        $centers = collect([
            ['Hyderabad Grain Market', 'Hyderabad, Sindh', 200, 40, 3, 4, 'grain,warehouse', 11],
            ['Sukkur Procurement Hub', 'Sukkur, Sindh', 160, 32, 2, 3, 'harvest,farm', 12],
            ['Multan Agri Mandi', 'Multan, Punjab', 260, 50, 4, 5, 'wheat,silo', 13],
        ])->map(fn ($c) => Center::create([
            'name' => $c[0], 'location' => $c[1], 'daily_capacity' => $c[2], 'slot_capacity' => $c[3],
            'weighing_stations' => $c[4], 'unloading_points' => $c[5], 'image' => $img($c[6], $c[7]),
            'storage_capacity' => $c[2] * 10, 'staff_available' => $c[4] * 3,
        ]));

        $crops = collect([['Wheat', 87500, 'wheat,field', 21], ['Rice', 118000, 'rice,paddy', 22], ['Cotton', 165000, 'cotton,plant', 23], ['Maize', 66000, 'corn,field', 24], ['Sugarcane', 5600, 'sugarcane', 25]])
            ->map(fn ($c) => Crop::create(['name' => $c[0], 'price_per_ton' => $c[1], 'image' => $img($c[2], $c[3])]));

        // Demo accounts (password for all: password)
        User::create(['name' => 'Aisha Management', 'email' => 'admin@agriqueue.test', 'password' => 'password', 'role' => 'admin']);
        User::create(['name' => 'Bilal Staff', 'email' => 'staff@agriqueue.test', 'password' => 'password', 'role' => 'staff', 'center_id' => $centers[0]->id]);
        User::create(['name' => 'Sana Staff', 'email' => 'staff2@agriqueue.test', 'password' => 'password', 'role' => 'staff', 'center_id' => $centers[1]->id]);
        $inspector = User::create(['name' => 'Imran Inspector', 'email' => 'inspector@agriqueue.test', 'password' => 'password', 'role' => 'inspector', 'center_id' => $centers[0]->id]);

        $farmers = collect([
            ['Ghulam Rasool', 'farmer@agriqueue.test'], ['Zahid Mehmood', 'zahid@agriqueue.test'], ['Nadia Bibi', 'nadia@agriqueue.test'],
            ['Khalid Mallah', 'khalid@agriqueue.test'], ['Farzana Shah', 'farzana@agriqueue.test'], ['Abdul Samad', 'samad@agriqueue.test'],
        ])->map(fn ($f, $i) => User::create([
            'name' => $f[0], 'email' => $f[1], 'password' => 'password', 'role' => 'farmer', 'phone' => '0300123' . (4400 + $i),
            'location' => ['Tando Allahyar', 'Matiari', 'Khairpur', 'Larkana', 'Badin', 'Mirpurkhas'][$i], 'identification' => '4210' . (1000000 + $i * 7919),
        ]));

        $slots = new SlotService();
        $make = function (Center $c, string $date, string $slot, Crop $crop, User $f, float $qty, string $status, array $extra = []) use ($slots) {
            return Booking::create($extra + [
                'farmer_id' => $f->id, 'center_id' => $c->id, 'crop_id' => $crop->id, 'estimated_quantity' => $qty, 'booking_date' => $date,
                'time_slot' => $slot, 'token_number' => $slots->nextToken($c, $date), 'queue_status' => $status,
                'vehicle_number' => 'SND-' . rand(1000, 9999),
            ]);
        };
        $proc = function (Booking $b, array $d = []) {
            $net = (int) ($b->estimated_quantity * 1000 * (rand(94, 104) / 100));
            $empty = rand(1900, 2600);
            $grade = ['A', 'A', 'B', 'C'][rand(0, 3)];
            $factor = ['A' => 1, 'B' => .95, 'C' => .9][$grade];
            $price = $b->crop->price_per_ton * $factor;
            return Procurement::create($d + [
                'booking_id' => $b->id, 'gross_weight' => $net + $empty, 'empty_weight' => $empty, 'actual_weight' => $net,
                'weighing_station' => 'WS-' . rand(1, 3), 'weighed_at' => $b->queued_at?->copy()->addMinutes(rand(10, 35)),
                'quality_grade' => $grade, 'moisture' => rand(90, 135) / 10, 'damaged_percent' => rand(5, 40) / 10,
                'foreign_material' => rand(2, 20) / 10, 'quality_status' => 'accepted', 'price_per_unit' => $price,
                'total_amount' => round($price * $net / 1000, 2),
            ]);
        };

        // 14 days of completed history for the analytics charts
        foreach ($centers as $c) {
            for ($d = 14; $d >= 1; $d--) {
                $date = today()->subDays($d)->toDateString();
                for ($i = 0; $i < rand(3, 6); $i++) {
                    $in = today()->subDays($d)->setTime(rand(8, 15), rand(0, 59));
                    $b = $make($c, $date, SlotService::SLOTS[rand(0, 7)], $crops[rand(0, 4)], $farmers[rand(0, 5)], rand(8, 30), 'completed', ['checked_in_at' => $in, 'queued_at' => $in->copy()->addMinutes(2)]);
                    $proc($b, ['payment_status' => 'paid', 'completion_time' => $in->copy()->addMinutes(rand(45, 110)), 'receipt_number' => 'RC-' . $b->id . '-H']);
                }
            }
        }

        // A live day at Hyderabad: every stage of the flow is represented
        $c = $centers[0];
        $today = today()->toDateString();
        $stages = [
            ['08:00-09:00', 'completed'], ['09:00-10:00', 'payment_pending'], ['09:00-10:00', 'unloading'], ['10:00-11:00', 'quality_check'],
            ['10:00-11:00', 'weighing'], ['10:00-11:00', 'waiting'], ['11:00-12:00', 'waiting'], ['11:00-12:00', 'checked_in'],
            ['12:00-13:00', 'booked'], ['13:00-14:00', 'booked'], ['14:00-15:00', 'delayed'],
        ];
        foreach ($stages as $i => [$slot, $status]) {
            $f = $farmers[$i % 6];
            $arrived = in_array($status, ['booked', 'delayed'], true) ? [] : ['checked_in_at' => now()->subMinutes(120 - $i * 9), 'queued_at' => $status === 'checked_in' ? null : now()->subMinutes(118 - $i * 9)];
            $b = $make($c, $today, $slot, $crops[$i % 3], $f, rand(8, 18), $status, $arrived);
            if (in_array($status, ['quality_check', 'unloading', 'payment_pending', 'completed'], true)) {
                $p = $proc($b, ['weighed_at' => now()->subMinutes(60 - $i * 4), 'payment_status' => $status === 'completed' ? 'paid' : 'pending']);
                if ($status === 'quality_check') $p->update(['quality_grade' => null, 'moisture' => null, 'damaged_percent' => null, 'foreign_material' => null, 'quality_status' => null, 'price_per_unit' => null, 'total_amount' => null]);
                if ($status === 'unloading') $p->update(['price_per_unit' => null, 'total_amount' => null]);
                if ($status === 'completed') $p->update(['completion_time' => now()->subMinutes(20), 'receipt_number' => 'RC-' . now()->format('ymd') . '-DEMO1']);
            }
        }

        // Rejected produce example for the dashboard
        $rej = $make($centers[1], today()->subDay()->toDateString(), '10:00-11:00', $crops[1], $farmers[2], 12, 'rejected', ['checked_in_at' => now()->subDay(), 'queued_at' => now()->subDay()]);
        $proc($rej, ['quality_status' => 'rejected', 'quality_grade' => 'C', 'moisture' => 18.5, 'inspector_remarks' => 'Moisture too high for storage.', 'inspector_id' => $inspector->id, 'price_per_unit' => null, 'total_amount' => null]);

        Notice::send($farmers[0]->id, 'Welcome to AgriQueue. Book a slot to get your digital token.');
    }
}
