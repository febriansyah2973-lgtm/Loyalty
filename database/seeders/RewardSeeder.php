<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengimplementasikan katalog reward & privilege sesuai proposal Skema Membership Khanaan:
     * - Reguler: Free Charge Layanan Custom / Vermak Khusus
     * - Silver: Private Invitation Acara Eksklusif, Privilege Discount Private Sale
     * - Gold: Exclusive Hampers & Gift, Redeem Point Produk Eksklusif (Scarf & RTW Piece)
     */
    public function run(): void
    {
        $rewards = [
            [
                'title' => 'Free Charge Layanan Custom & Vermak Khusus',
                'category' => 'service',
                'description' => 'Gratis biaya layanan penyesuaian / vermak khusus untuk busana pada personal list Anda setiap kali pembelian di boutique Khanaan.',
                'points_required' => 50,
                'tier_required' => 'Reguler',
                'badge_text' => 'Layanan Butik',
                'stock' => 500,
            ],
            [
                'title' => 'Private Invitation: Acara Eksklusif Khanaan',
                'category' => 'invitation',
                'description' => 'Undangan VIP privat ke peragaan busana (runway show), trunk show, dan peluncuran koleksi adibusana terbaru Khanaan.',
                'points_required' => 150,
                'tier_required' => 'Silver',
                'badge_text' => 'Undangan VIP',
                'stock' => 100,
            ],
            [
                'title' => 'Privilege Access: Private Sale Koleksi Khanaan',
                'category' => 'privilege',
                'description' => 'Akses eksklusif berbelanja private sale untuk koleksi terpilih Khanaan dengan harga khusus sebelum dirilis ke publik.',
                'points_required' => 250,
                'tier_required' => 'Silver',
                'badge_text' => 'Akses Private',
                'stock' => 100,
            ],
            [
                'title' => 'Exclusive Hampers Hari Raya Khanaan',
                'category' => 'hampers',
                'description' => 'Bingkisan hampers mewah edisi hari raya istimewa yang dirancang dan dikurasi eksklusif langsung oleh atelier Khanaan.',
                'points_required' => 500,
                'tier_required' => 'Gold',
                'badge_text' => 'Hampers Mewah',
                'stock' => 50,
            ],
            [
                'title' => 'Special Anniversary & Birthday Signature Gift',
                'category' => 'hampers',
                'description' => 'Bingkisan hadiah spesial eksklusif ulang tahun / hari jadi butik khusus untuk member kehormatan Khanaan.',
                'points_required' => 400,
                'tier_required' => 'Gold',
                'badge_text' => 'Hadiah Spesial',
                'stock' => 50,
            ],
            [
                'title' => 'Produk Eksklusif: Khanaan Signature Silk Scarf',
                'category' => 'product',
                'description' => 'Hak penuh penukaran poin dengan scarf sutra motif signature adibusana Khanaan berbahan 100% Pure Silk premium.',
                'points_required' => 750,
                'tier_required' => 'Gold',
                'badge_text' => 'Produk Eksklusif',
                'stock' => 40,
            ],
            [
                'title' => 'Produk Eksklusif: Khanaan Ready-to-Wear Atelier Piece',
                'category' => 'product',
                'description' => 'Hak penuh penukaran poin dengan koleksi busana signature ready-to-wear edisi runway pilihan dari atelier Khanaan.',
                'points_required' => 1500,
                'tier_required' => 'Gold',
                'badge_text' => 'Atelier Piece',
                'stock' => 20,
            ],
            [
                'title' => 'Privilege Shopping Voucher Rp 500.000',
                'category' => 'discount',
                'description' => 'Voucher apresiasi potongan belanja senilai Rp 500.000 untuk transaksi koleksi Khanaan.',
                'points_required' => 200,
                'tier_required' => 'Silver',
                'badge_text' => 'Shopping Voucher',
                'stock' => 80,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::updateOrCreate(
                ['title' => $reward['title']],
                $reward
            );
        }
    }
}
