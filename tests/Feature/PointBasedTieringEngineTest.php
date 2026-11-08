<?php

namespace Tests\Feature;

use App\Models\LoyaltyClaim;
use App\Models\Reward;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointBasedTieringEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Store Manager']);
        Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer / Member']);
    }

    /**
     * 1. Database Adjustments (Crucial Change)
     * Verifies that points and lifetime_points exist, default correctly,
     * and new users start with tier 'Reguler'.
     */
    public function test_user_initializes_with_points_lifetime_points_and_reguler_tier(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->points);
        $this->assertEquals(0, $user->lifetime_points);
        $this->assertEquals('Reguler', $user->tier);
    }

    /**
     * 2. Tier Calculation Logic (Thresholds)
     * >= 0 AND <= 499   -> Reguler
     * >= 500 AND <= 999 -> Silver
     * >= 1000           -> Gold
     */
    public function test_tier_calculation_thresholds_based_on_lifetime_points(): void
    {
        // Reguler boundary
        $user1 = User::factory()->create(['lifetime_points' => 0]);
        $user1->updateTier();
        $this->assertEquals('Reguler', $user1->tier);

        $user2 = User::factory()->create(['lifetime_points' => 499]);
        $user2->updateTier();
        $this->assertEquals('Reguler', $user2->tier);

        // Silver boundary
        $user3 = User::factory()->create(['lifetime_points' => 500]);
        $user3->updateTier();
        $this->assertEquals('Silver', $user3->tier);

        $user4 = User::factory()->create(['lifetime_points' => 999]);
        $user4->updateTier();
        $this->assertEquals('Silver', $user4->tier);

        // Gold boundary
        $user5 = User::factory()->create(['lifetime_points' => 1000]);
        $user5->updateTier();
        $this->assertEquals('Gold', $user5->tier);

        $user6 = User::factory()->create(['lifetime_points' => 5000]);
        $user6->updateTier();
        $this->assertEquals('Gold', $user6->tier);
    }

    /**
     * 3. Point Calculation Engine (Earning Points)
     * Base Formula: (Transaction Amount / 100,000) * 10
     * Multipliers:
     * Reguler -> 1.0
     * Silver  -> 1.5
     * Gold    -> 2.0
     */
    public function test_point_calculation_engine_formulas_and_multipliers(): void
    {
        $reguler = User::factory()->create(['tier' => 'Reguler']);
        $silver = User::factory()->create(['tier' => 'Silver']);
        $gold = User::factory()->create(['tier' => 'Gold']);

        // Transaction Amount: Rp 1,000,000 -> Base = (1,000,000 / 100,000) * 10 = 100 points
        $this->assertEquals(1.0, $reguler->getTierMultiplier());
        $this->assertEquals(100, $reguler->calculateEarnedPoints(1000000)); // 100 * 1.0 = 100

        $this->assertEquals(1.5, $silver->getTierMultiplier());
        $this->assertEquals(150, $silver->calculateEarnedPoints(1000000)); // 100 * 1.5 = 150

        $this->assertEquals(2.0, $gold->getTierMultiplier());
        $this->assertEquals(200, $gold->calculateEarnedPoints(1000000)); // 100 * 2.0 = 200

        // Transaction Amount: Rp 250,000 -> Base = 25 points
        $this->assertEquals(25, $reguler->calculateEarnedPoints(250000));
        $this->assertEquals(37, $silver->calculateEarnedPoints(250000)); // 25 * 1.5 = 37.5 -> 37 floor
        $this->assertEquals(50, $gold->calculateEarnedPoints(250000)); // 25 * 2.0 = 50
    }

    /**
     * 3. Database Injection:
     * When points are earned via claim approval, update BOTH:
     * points = points + calculated_points
     * lifetime_points = lifetime_points + calculated_points
     * and automatically trigger tier upgrades.
     */
    public function test_claim_approval_updates_both_columns_and_triggers_tier_upgrade(): void
    {
        $admin = User::factory()->admin()->create();

        // Customer starts at Reguler with 450 points
        $customer = User::factory()->create([
            'points' => 450,
            'lifetime_points' => 450,
            'tier' => 'Reguler',
        ]);

        // Customer spends Rp 1,000,000
        // Base = (1,000,000 / 100,000) * 10 = 100
        // Current tier is Reguler -> Multiplier 1.0 -> 100 calculated points
        $claim = LoyaltyClaim::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-TIER-UPGRADE',
            'amount' => 1000000,
            'proof_path' => 'proofs/test.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.claims.approve', $claim->id));

        $customer->refresh();

        // points: 450 + 100 = 550
        $this->assertEquals(550, $customer->points);
        // lifetime_points: 450 + 100 = 550
        $this->assertEquals(550, $customer->lifetime_points);
        // Tier upgrade from Reguler to Silver (550 is >= 500)
        $this->assertEquals('Silver', $customer->tier);

        // Next transaction as Silver member: Rp 3,000,000
        // Base = (3,000,000 / 100,000) * 10 = 300
        // Current tier is Silver -> Multiplier 1.5 -> 300 * 1.5 = 450 calculated points
        $claim2 = LoyaltyClaim::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-TIER-GOLD',
            'amount' => 3000000,
            'proof_path' => 'proofs/test2.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.claims.approve', $claim2->id));

        $customer->refresh();

        // points: 550 + 450 = 1000
        $this->assertEquals(1000, $customer->points);
        // lifetime_points: 550 + 450 = 1000
        $this->assertEquals(1000, $customer->lifetime_points);
        // Tier upgrade from Silver to Gold (1000 is >= 1000)
        $this->assertEquals('Gold', $customer->tier);
    }

    /**
     * 4. Redemption Engine (Point Deduction)
     * When a Gold member redeems a gift:
     * Deduct from points (points = points - reward.points_required).
     * DO NOT touch lifetime_points.
     * This ensures their tier remains safe even if their active balance drops to 0.
     */
    public function test_redemption_deducts_spendable_points_only_and_preserves_gold_tier(): void
    {
        // Gold member with 1,000 lifetime points and 500 active spendable points
        $goldMember = User::factory()->create([
            'points' => 500,
            'lifetime_points' => 1000,
            'tier' => 'Gold',
        ]);

        $reward = Reward::create([
            'title' => 'Voucher Hadiah Gold 500 Poin',
            'description' => 'Eksklusif voucher belanja hadiah',
            'points_required' => 500,
            'stock' => 5,
        ]);

        $this->actingAs($goldMember)->post(route('rewards.redeem', $reward->id));

        $goldMember->refresh();

        // 1. Active points deducted down to 0
        $this->assertEquals(0, $goldMember->points);

        // 2. Lifetime points strictly untouched
        $this->assertEquals(1000, $goldMember->lifetime_points);

        // 3. Tier remains Gold (NO DOWNGRADE!)
        $this->assertEquals('Gold', $goldMember->tier);

        // Verify voucher created
        $this->assertDatabaseHas('vouchers', [
            'user_id' => $goldMember->id,
            'reward_id' => $reward->id,
        ]);

        // Verify point ledger records negative mutation
        $this->assertDatabaseHas('point_ledgers', [
            'user_id' => $goldMember->id,
            'type' => 'redeem_voucher',
            'amount' => -500,
            'balance_after' => 0,
        ]);
    }
}
