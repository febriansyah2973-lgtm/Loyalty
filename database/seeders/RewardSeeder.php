<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Reward::updateOrCreate([
            'title' => 'Voucher Diskon Rp 200.000',
        ], [
            'title' => 'Voucher Diskon Rp 200.000',
            'description' => 'Potongan harga langsung senilai Rp 200.000 untuk seluruh koleksi Khanaan.',
            'points_required' => 50,
            'stock' => 100,
        ]);

        \App\Models\Reward::updateOrCreate([
            'title' => 'Voucher Diskon Rp 500.000',
        ], [
            'title' => 'Voucher Diskon Rp 500.000',
            'description' => 'Potongan harga langsung senilai Rp 500.000 untuk transaksi belanja Anda.',
            'points_required' => 100,
            'stock' => 100,
        ]);

        \App\Models\Reward::updateOrCreate([
            'title' => 'Voucher Diskon Rp 1.000.000',
        ], [
            'title' => 'Voucher Diskon Rp 1.000.000',
            'description' => 'Potongan harga eksklusif senilai Rp 1.000.000 untuk belanja koleksi Khanaan.',
            'points_required' => 200,
            'stock' => 50,
        ]);
    }
}
