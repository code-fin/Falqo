<?php

namespace Tests\Feature;

use App\Livewire\Workspace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_authenticated_users_can_create_a_customer(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Workspace::class)
            ->set('name', 'Acme Studio')
            ->set('email', 'hello@acme.test')
            ->call('addCustomer')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'user_id' => $user->id,
            'name' => 'Acme Studio',
            'email' => 'hello@acme.test',
        ]);
    }
}
