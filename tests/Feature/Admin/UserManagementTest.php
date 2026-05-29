<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_super_admin_cannot_be_deleted(): void
    {
        $super = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($super);

        $response = $this->from('/admin/users')->delete("/admin/users/{$super->id}");

        $response->assertRedirect('/admin/users');
        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $super->id]);
    }

    public function test_super_admin_can_create_admin_user(): void
    {
        $super = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($super);

        $response = $this->post('/admin/users', [
            'name' => 'Operador LBC',
            'email' => 'operador@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => '1',
            'password' => 'PasswordSeguro123',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'operador@example.com',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }
}
