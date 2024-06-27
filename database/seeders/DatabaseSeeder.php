<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Setting;
use App\Models\User;
use App\Models\BinanceFee;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $admin = new Admin();
        $admin->name = 'admin';
        $admin->email = 'admin@yelogift.com';
        $admin->password = bcrypt('admin');
        $admin->save();


        $setting = new Setting();
        $setting->key = 'site-name';
        $setting->value = 'yelogift';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'logo';
        $setting->value = 'main-logo.png';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'favicon';
        $setting->value = 'main-logo.png';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'main-light-color';
        $setting->value = '#ffffff';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'main-dark-color';
        $setting->value = '#0C192B';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'primary-light-color';
        $setting->value = '#F9D423';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'primary-dark-color';
        $setting->value = '#F9D423';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'footer-text';
        $setting->value = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'email-enable';
        $setting->value = '1';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'insite-enable';
        $setting->value = '1';
        $setting->save();

        $setting = new Setting();
        $setting->key = 'manual-enable';
        $setting->value = '1';
        $setting->save();

        $fee = new BinanceFee();
        $fee->description = 'binanace-fee';
        $fee->save();




    }
}
