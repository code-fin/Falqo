<?php

namespace Tests\Feature;

use App\Livewire\Workspace;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\DemoWorkspaceSeeder;
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

    public function test_user_can_log_time_against_their_ticket(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['user_id' => $user->id, 'name' => 'Acme']);
        $project = Project::create(['customer_id' => $customer->id, 'name' => 'Website']);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'project_id' => $project->id, 'assigned_user_id' => $user->id, 'time_bookmarked' => true, 'title' => 'Fix navigation']);

        Livewire::actingAs($user)
            ->test(Workspace::class)
            ->set('ticketId', $ticket->id)
            ->set('entryDate', today()->toDateString())
            ->set('entryHours', 1)
            ->set('entryMinutes', 30)
            ->set('description', 'Implemented and tested the navigation fix.')
            ->call('saveTicketTime')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('time_entries', ['user_id' => $user->id, 'ticket_id' => $ticket->id]);
    }

    public function test_time_booking_requires_a_reason(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['user_id' => $user->id, 'name' => 'Acme']);
        $project = Project::create(['customer_id' => $customer->id, 'name' => 'Website']);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'project_id' => $project->id, 'assigned_user_id' => $user->id, 'time_bookmarked' => true, 'title' => 'Fix navigation']);

        Livewire::actingAs($user)->test(Workspace::class)
            ->set('ticketId', $ticket->id)->set('entryDate', today()->toDateString())
            ->set('entryHours', 1)->set('description', '')
            ->call('saveTicketTime')->assertHasErrors(['description' => 'required']);
    }

    public function test_user_can_add_and_edit_multiple_time_entries_for_the_same_ticket_and_day(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create(['user_id' => $user->id, 'name' => 'Acme']);
        $project = Project::create(['customer_id' => $customer->id, 'name' => 'Website']);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'project_id' => $project->id, 'assigned_user_id' => $user->id, 'time_bookmarked' => true, 'title' => 'Fix navigation']);

        $component = Livewire::actingAs($user)->test(Workspace::class)
            ->set('ticketId', $ticket->id)->set('entryDate', today()->toDateString())
            ->set('entryMinutes', 30)->set('description', 'Investigated the navigation issue.')
            ->call('saveTicketTime')->assertHasNoErrors()
            ->call('prepareNewTimeEntry')
            ->set('entryHours', 1)->set('description', 'Implemented the navigation fix.')
            ->call('saveTicketTime')->assertHasNoErrors();

        $this->assertDatabaseCount('time_entries', 2);

        $entry = TimeEntry::where('description', 'Investigated the navigation issue.')->firstOrFail();
        $component->call('editTimeEntry', $entry->id)
            ->set('entryMinutes', 45)->set('description', 'Investigated and documented the issue.')
            ->call('saveTicketTime')->assertHasNoErrors();

        $this->assertDatabaseHas('time_entries', ['id' => $entry->id, 'description' => 'Investigated and documented the issue.']);
    }

    public function test_demo_seeder_populates_calendar_and_timesheet_data(): void
    {
        $this->seed(DemoWorkspaceSeeder::class);

        $this->assertDatabaseCount('customers', 4);
        $this->assertDatabaseCount('projects', 5);
        $this->assertDatabaseCount('tickets', 10);
        $this->assertDatabaseHas('tasks', ['title' => 'Prepare homepage wireframes']);
        $this->assertDatabaseHas('time_entries', ['user_id' => User::where('email', 'test@example.com')->value('id')]);
    }
}
