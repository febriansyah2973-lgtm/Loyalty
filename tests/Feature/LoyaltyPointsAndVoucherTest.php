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

    public function test_purchase_claim_approval_awards_10_points_per_1_million_and_logs_ledger(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['points' => 0, 'cashback_balance' => 0]);

        $claim1 = LoyaltyClaim::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-1000K',
            'amount' => 1000000,
            'proof_path' => 'proofs/dummy1.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.claims.approve', $claim1->id));
        $user->refresh();
        $this->assertEquals(10, $user->points);
        $this->assertDatabaseHas('point_ledgers', [
            'user_id' => $user->id,
            'type' => 'earn_claim',
            'amount' => 10,
            'balance_after' => 10,
        ]);
    }

    public function test_rewards_redemption_schemes_and_deducts_via_ledger(): void
    {
        $user = User::factory()->create(['points' => 350]);

        $reward50 = Reward::create(['title' => 'Voucher Diskon Rp 200.000', 'points_required' => 50, 'stock' => 10]);
        $reward100 = Reward::create(['title' => 'Voucher Diskon Rp 500.000', 'points_required' => 100, 'stock' => 10]);
        $reward200 = Reward::create(['title' => 'Voucher Diskon Rp 1.000.000', 'points_required' => 200, 'stock' => 10]);

        // Redeem 50 pts
        $this->actingAs($user)->post(route('rewards.redeem', $reward50->id));
        $user->refresh();
        $this->assertEquals(300, $user->points);

        // Redeem 100 pts
        $this->actingAs($user)->post(route('rewards.redeem', $reward100->id));
        $user->refresh();
        $this->assertEquals(200, $user->points);

        // Redeem 200 pts
        $this->actingAs($user)->post(route('rewards.redeem', $reward200->id));
        $user->refresh();
        $this->assertEquals(0, $user->points);

        $this->assertEquals(3, $user->vouchers()->count());
        $this->assertEquals(3, $user->pointLedgers()->where('type', 'redeem_voucher')->count());
    }

    public function test_customer_is_redirected_by_role_middleware_when_accessing_admin_routes(): void
    {
        $customer = User::factory()->create();

        $response = $this->actingAs($customer)->get(route('admin.vouchers.index'));
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }
}
