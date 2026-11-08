<?php

namespace Tests\Feature;

use App\Models\LoyaltyClaim;
use App\Models\Reward;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyPointsAndVoucherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Store Manager']);
        Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer / Member']);
    }

    public function test_admin_can_issue_specific_voucher_to_one_user(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['name' => 'Budi VIP']);
        $otherUser = User::factory()->create(['name' => 'Siti']);

        $response = $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'user_id' => $targetUser->id,
            'code' => 'KHN-BUDI-SPECIAL',
            'voucher_type' => 'custom',
            'custom_title' => 'Voucher Diskon Spesial Rp 300.000',
            'discount_amount' => 300000,
            'expiry_days' => 14,
            'notes' => 'Pemberian khusus loyal customer',
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseHas('vouchers', [
            'user_id' => $targetUser->id,
            'code' => 'KHN-BUDI-SPECIAL',
            'title' => 'Voucher Diskon Spesial Rp 300.000',
        ]);

        $this->assertEquals(1, $targetUser->vouchers()->count());
        $this->assertEquals(0, $otherUser->vouchers()->count());
    }

    public function test_purchase_claim_approval_awards_points_via_point_calculation_engine_and_logs_ledger(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['points' => 0, 'lifetime_points' => 0, 'tier' => 'Reguler', 'cashback_balance' => 0]);

        $claim1 = LoyaltyClaim::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-1000K',
            'amount' => 1000000,
            'proof_path' => 'proofs/dummy1.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.claims.approve', $claim1->id));
        $user->refresh();

        // Formula: (1,000,000 / 100,000) * 10 * 1.0 (Reguler) = 100 points
        $this->assertEquals(100, $user->points);
        $this->assertEquals(100, $user->lifetime_points);
        $this->assertEquals('Reguler', $user->tier); // 100 is between 0 and 499

        $this->assertDatabaseHas('point_ledgers', [
            'user_id' => $user->id,
            'type' => 'earn_claim',
            'amount' => 100,
            'balance_after' => 100,
        ]);
    }

    public function test_rewards_redemption_schemes_and_deducts_via_ledger(): void
    {
        $user = User::factory()->create(['points' => 350, 'lifetime_points' => 1200, 'tier' => 'Gold']);

        $reward50 = Reward::create(['title' => 'Voucher Diskon Rp 200.000', 'description' => 'Voucher diskon 200k', 'points_required' => 50, 'stock' => 10]);
        $reward100 = Reward::create(['title' => 'Voucher Diskon Rp 500.000', 'description' => 'Voucher diskon 500k', 'points_required' => 100, 'stock' => 10]);
        $reward200 = Reward::create(['title' => 'Voucher Diskon Rp 1.000.000', 'description' => 'Voucher diskon 1jt', 'points_required' => 200, 'stock' => 10]);

        // Redeem 50 pts
        $this->actingAs($user)->post(route('rewards.redeem', $reward50->id));
        $user->refresh();
        $this->assertEquals(300, $user->points);
        $this->assertEquals(1200, $user->lifetime_points); // Lifetime points UNTOUCHED
        $this->assertEquals('Gold', $user->tier); // Tier remains Gold

        // Redeem 100 pts
        $this->actingAs($user)->post(route('rewards.redeem', $reward100->id));
        $user->refresh();
        $this->assertEquals(200, $user->points);
        $this->assertEquals(1200, $user->lifetime_points);
        $this->assertEquals('Gold', $user->tier);

        // Redeem 200 pts (balance drops to 0!)
        $this->actingAs($user)->post(route('rewards.redeem', $reward200->id));
        $user->refresh();
        $this->assertEquals(0, $user->points);
        $this->assertEquals(1200, $user->lifetime_points); // UNTOUCHED
        $this->assertEquals('Gold', $user->tier); // Stays Gold even when active points drop to 0!

        $this->assertEquals(3, $user->vouchers()->count());
        $this->assertEquals(3, $user->pointLedgers()->where('type', 'redeem_voucher')->count());
    }

    public function test_admin_can_issue_service_or_hampers_voucher_with_category(): void
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create(['name' => 'Bambang VIP']);

        $response = $this->actingAs($admin)->post(route('admin.vouchers.store'), [
            'user_id' => $targetUser->id,
            'code' => 'KHN-HAMPERS-RAYA',
            'voucher_type' => 'custom',
            'category' => 'hampers',
            'custom_title' => 'Exclusive Raya Hampers & Gift Box',
            'discount_amount' => 0,
            'expiry_days' => 30,
            'notes' => 'Klaim bingkisan eksklusif hari raya di boutique Khanaan',
        ]);

        $response->assertRedirect(route('admin.vouchers.index'));
        $this->assertDatabaseHas('vouchers', [
            'user_id' => $targetUser->id,
            'code' => 'KHN-HAMPERS-RAYA',
            'category' => 'hampers',
            'title' => 'Exclusive Raya Hampers & Gift Box',
        ]);

        $voucher = Voucher::where('code', 'KHN-HAMPERS-RAYA')->first();
        $this->assertEquals('hampers', $voucher->category);
        $this->assertEquals('Hampers & Gift', $voucher->categoryLabel());
    }

    public function test_gold_exclusive_reward_cannot_be_redeemed_by_reguler_member(): void
    {
        $regulerUser = User::factory()->create(['points' => 1500, 'lifetime_points' => 400, 'tier' => 'Reguler']);

        $goldReward = Reward::create([
            'title' => 'Khanaan Signature Silk Scarf (Exclusive)',
            'description' => 'Redeemable Silk Scarf for Gold Member',
            'points_required' => 1000,
            'stock' => 5,
            'category' => 'product',
            'tier_required' => 'Gold',
        ]);

        $response = $this->actingAs($regulerUser)->from(route('dashboard'))->post(route('rewards.redeem', $goldReward->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
        $regulerUser->refresh();
        $this->assertEquals(1500, $regulerUser->points); // Points NOT deducted
        $this->assertEquals(0, $regulerUser->vouchers()->count());
    }

    public function test_gold_exclusive_reward_can_be_redeemed_by_gold_member(): void
    {
        $goldUser = User::factory()->create(['points' => 1500, 'lifetime_points' => 1500, 'tier' => 'Gold']);

        $goldReward = Reward::create([
            'title' => 'Khanaan Signature Silk Scarf (Exclusive)',
            'description' => 'Redeemable Silk Scarf for Gold Member',
            'points_required' => 1000,
            'stock' => 5,
            'category' => 'product',
            'tier_required' => 'Gold',
        ]);

        $response = $this->actingAs($goldUser)->from(route('dashboard'))->post(route('rewards.redeem', $goldReward->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $goldUser->refresh();
        $this->assertEquals(500, $goldUser->points); // 1500 - 1000 = 500
        $this->assertEquals(1500, $goldUser->lifetime_points); // Lifetime points safe!
        $this->assertEquals('Gold', $goldUser->tier); // Still Gold!
        $this->assertEquals(1, $goldUser->vouchers()->count());
    }

    public function test_customer_is_redirected_by_role_middleware_when_accessing_admin_routes(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get(route('admin.vouchers.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }
}

