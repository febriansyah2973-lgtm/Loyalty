<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_staff_management_and_create_another_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Staff & User Management', false);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Second Administrator',
                'email' => 'admin2@example.com',
                'password' => 'A-strong-password-2026',
                'role_id' => $adminRole->id,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin2@example.com',
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_manager_cannot_open_staff_management(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_configured_super_admin_cannot_be_demoted_or_deleted(): void
    {
        config(['super-admin.email' => 'superadmin@khanaan.com']);

        $admin = User::factory()->admin()->create();
        $superAdmin = User::factory()->admin()->create(['email' => 'superadmin@khanaan.com']);
        $managerRole = Role::where('name', 'manager')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.users.update-role', $superAdmin), ['role_id' => $managerRole->id])
            ->assertSessionHas('error');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $superAdmin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'role_id' => $superAdmin->role_id,
        ]);
    }

    public function test_new_staff_password_must_be_at_least_twelve_characters(): void
    {
        $admin = User::factory()->admin()->create();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Weak Password Account',
                'email' => 'weak@example.com',
                'password' => 'short',
                'role_id' => $adminRole->id,
            ])
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);
    }

    public function test_admin_can_update_user_details_and_change_password(): void
    {
        $admin = User::factory()->admin()->create();
        $customerRole = Role::where('name', 'customer')->firstOrFail();
        $managerRole = Role::where('name', 'manager')->firstOrFail();

        $targetUser = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old_email@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('OldPassword123'),
            'role_id' => $customerRole->id,
            'points' => 50,
            'lifetime_points' => 50,
            'tier' => 'Reguler',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => 'new_email@example.com',
            'password' => 'BrandNewPassword2026',
            'role_id' => $managerRole->id,
            'points' => 800,
            'lifetime_points' => 800,
            'tier' => 'Silver',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $targetUser->refresh();
        $this->assertEquals('Updated Name', $targetUser->name);
        $this->assertEquals('new_email@example.com', $targetUser->email);
        $this->assertEquals($managerRole->id, $targetUser->role_id);
        $this->assertEquals(800, $targetUser->points);
        $this->assertEquals(800, $targetUser->lifetime_points);
        $this->assertEquals('Silver', $targetUser->tier);

        // Verify password actually updated
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('BrandNewPassword2026', $targetUser->password));
    }

    public function test_admin_can_update_user_without_changing_password(): void
    {
        $admin = User::factory()->admin()->create();
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $targetUser = User::factory()->create([
            'name' => 'User Without Password Change',
            'email' => 'keep_password@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('OriginalSecretPass'),
            'role_id' => $customerRole->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Name Changed',
            'email' => 'keep_password@example.com',
            'password' => '', // Leave blank
            'role_id' => $customerRole->id,
            'points' => 150,
            'lifetime_points' => 150,
        ]);

        $response->assertRedirect();
        $targetUser->refresh();

        $this->assertEquals('Name Changed', $targetUser->name);
        // Original password must remain valid
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('OriginalSecretPass', $targetUser->password));
    }
}