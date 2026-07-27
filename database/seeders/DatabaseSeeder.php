<?php

namespace Database\Seeders;

use App\Models\{User, Vehicle, Lead, Setting};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── ADMIN ACCOUNT ───────────────────────────────────────
        User::updateOrCreate(['email'=>'admin@naa.test'], [
            'name'=>'Administrator',
            'password'=>Hash::make('password'),
            'role'=>'admin',
            'verified'=>true,
            'status'=>'active',
            'phone'=>'(708) 555-1200',
            'city'=>'Beecher, IL',
        ]);

        // ── A VERIFIED TEST BIDDER ──────────────────────────────
        User::updateOrCreate(['email'=>'buyer@naa.test'], [
            'name'=>'Grant Holloway',
            'password'=>Hash::make('password'),
            'role'=>'bidder',
            'verified'=>true,
            'status'=>'active',
            'phone'=>'(314) 555-0917',
            'city'=>'St. Louis, MO',
            'source'=>'Direct',
        ]);

        // ── A PENDING BIDDER (to test KYC approval) ─────────────
        User::updateOrCreate(['email'=>'pending@naa.test'], [
            'name'=>'Darnell Pierce',
            'password'=>Hash::make('password'),
            'role'=>'bidder',
            'verified'=>false,
            'status'=>'pending',
            'phone'=>'(312) 555-0184',
            'city'=>'Chicago, IL',
            'source'=>'Google',
        ]);

        // ── VEHICLES ────────────────────────────────────────────
        $cars = [
            ['62',2023,'Chevrolet','Corvette Z06','6.2L V8','1G1YF2D38P5603897',4200,47200,51000,0,'live',true],
            ['70',2024,'Mercedes-Benz','GLE 63 S','4.0L V8','4JGFB8KB6RB109609',9100,36900,44000,73800,'live',true],
            ['81',2023,'Ford','F-150 Raptor R','5.2L V8','1FTFW1RJ8PFC65671',14800,52000,61000,69300,'upcoming',false],
            ['14',2021,'Ferrari','F8 Spider','3.9L V8','ZFF93LMAXM0262151',6400,164000,178000,0,'upcoming',true],
            ['10',2023,'Audi','S5 Premium Plus','3.0L V6','WAUC4CF52PA055427',18700,19400,24000,42700,'upcoming',false],
            ['45',2025,'BMW','M5','4.4L V8','WBS83CH0XSC000112',3100,42000,53000,64500,'upcoming',true],
            ['52',2019,'Porsche','911 GT3','4.0L H6','WP0AC2A99KS000221',14900,36000,58000,0,'upcoming',false],
            ['92',2018,'Land Rover','Range Rover Sport','5.0L V8','SALWR2RE0JA000441',52100,22000,34000,0,'upcoming',false],
        ];

        foreach ($cars as [$lot,$yr,$mk,$md,$eng,$vin,$mi,$start,$res,$buy,$status,$hot]) {
            $v = Vehicle::updateOrCreate(['lot'=>$lot], [
                'year'=>$yr,'make'=>$mk,'model'=>$md,'engine'=>$eng,'vin'=>$vin,
                'miles'=>$mi,'start_bid'=>$start,'reserve'=>$res,'buy_now'=>$buy,
                'current_bid'=>0,'status'=>$status,'hot'=>$hot,
                'title_status'=>'Clean','location'=>'Beecher, IL',
                'transmission'=>'Automatic','color'=>'—',
                'agent'=>'Emma Clark',
            ]);
            // live lots get a 30-minute clock so you can bid straight away
            if ($status === 'live') {
                $v->update(['ends_at'=>now()->addMinutes(30)]);
            }
        }

        // ── SAMPLE LEADS ────────────────────────────────────────
        Lead::updateOrCreate(['email'=>'wes.k@outlook.com'], [
            'name'=>'Wes Kowalski','phone'=>'(773) 555-0488','subject'=>'Contact',
            'message'=>'Is the reserve negotiable on the GLE 63?','stage'=>'new','read'=>false,
        ]);
        Lead::updateOrCreate(['email'=>'m.diaz@gmail.com'], [
            'name'=>'Marcus Diaz','phone'=>'(312) 555-0733','subject'=>'Make Offer',
            'offer'=>44000,'vehicle_id'=>Vehicle::where('lot','81')->value('id'),
            'stage'=>'new','read'=>false,
        ]);

        // ── SETTINGS ────────────────────────────────────────────
        Setting::put('increment','100');
        Setting::put('anti_snipe','30');
        Setting::put('currency','$');

        $this->command->info('');
        $this->command->info('  Admin login:  admin@naa.test  / password');
        $this->command->info('  Buyer login:  buyer@naa.test  / password');
        $this->command->info('');
    }
}
