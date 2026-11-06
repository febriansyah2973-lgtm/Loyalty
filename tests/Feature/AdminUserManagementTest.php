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
            ->assertSee('Staff & User Management');

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
}