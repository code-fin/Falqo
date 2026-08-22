<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password'), 'email_verified_at' => now()],
        );
        $company = Company::firstOrCreate(['name' => 'Falqo Demo Company']);
        $user->update(['company_id' => $company->id]);
        $colleagues = collect([
            ['Mila de Vries', 'mila@example.com'],
            ['Noah Jansen', 'noah@example.com'],
        ])->map(fn (array $person) => User::updateOrCreate(['email' => $person[1]], ['company_id' => $company->id, 'name' => $person[0], 'password' => bcrypt('password'), 'email_verified_at' => now()]));

        $planning = CalendarEvent::updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Weekly planning'],
            ['created_by' => $user->id, 'description' => 'Review priorities and coordinate the week.', 'starts_at' => now()->startOfWeek()->addDays(1)->setTime(10, 0), 'ends_at' => now()->startOfWeek()->addDays(1)->setTime(10, 45), 'is_public' => true],
        );
        $planning->attendees()->sync($colleagues->prepend($user)->pluck('id'));

        $customers = collect([
            ['Northstar Studio', 'hello@northstar.test'],
            ['Lumen Commerce', 'team@lumen.test'],
            ['Harbor & Co.', 'contact@harbor.test'],
            ['Juniper Health', 'digital@juniper.test'],
        ])->map(fn (array $customer) => Customer::firstOrCreate(
            ['user_id' => $user->id, 'name' => $customer[0]],
            ['email' => $customer[1]],
        ));

        $projectData = [
            [0, 'Brand website refresh', 'A complete visual and content refresh for the public website.'],
            [1, 'Checkout optimization', 'Improve conversion and reduce checkout abandonment.'],
            [2, 'Client portal', 'A secure portal for documents, messages, and project updates.'],
            [3, 'Patient onboarding', 'Streamline the new-patient intake experience.'],
            [0, 'Q3 campaign', 'Landing pages and launch assets for the autumn campaign.'],
        ];
        $projectIcons = ['paint-brush', 'shopping-bag', 'code-bracket', 'building-office', 'megaphone'];
        $projects = collect($projectData)->map(fn (array $data, int $index) => Project::updateOrCreate(
            ['customer_id' => $customers[$data[0]]->id, 'name' => $data[1]],
            ['icon' => $projectIcons[$index], 'description' => $data[2], 'status' => 'active', 'due_date' => now()->addDays(random_int(20, 90))],
        ));

        $taskTitles = [
            'Review discovery notes', 'Prepare homepage wireframes', 'Write acceptance criteria',
            'Update mobile navigation', 'QA payment flow', 'Collect stakeholder feedback',
            'Polish empty states', 'Document API handoff', 'Run accessibility review',
            'Prepare launch checklist', 'Optimize image delivery', 'Review analytics events',
            'Design account settings', 'Test invitation flow', 'Finalize content migration',
        ];
        foreach ($taskTitles as $index => $title) {
            Task::updateOrCreate(
                ['project_id' => $projects[$index % $projects->count()]->id, 'title' => $title],
                [
                    'status' => $index % 5 === 0 ? 'done' : 'todo',
                    'assigned_user_id' => $user->id,
                    'due_date' => $index === 14 ? null : now()->startOfMonth()->addDays(($index * 3) % 35),
                    'estimated_minutes' => [30, 60, 90, 120, 180][$index % 5],
                ],
            );
        }

        $ticketTitles = [
            'Hero image crops incorrectly on tablet', 'Add invoice download to account page',
            'Checkout confirmation email is delayed', 'Update privacy policy link in footer',
            'Portal invitation expires too quickly', 'Add search to document library',
            'Form validation message overlaps input', 'Export analytics for monthly report',
            'New campaign UTM parameters', 'Improve keyboard focus on modal',
        ];
        $tickets = collect($ticketTitles)->map(function (string $title, int $index) use ($projects, $user) {
            $project = $projects[$index % $projects->count()];

            return Ticket::updateOrCreate(
                ['customer_id' => $project->customer_id, 'title' => $title],
                ['project_id' => $project->id, 'assigned_user_id' => $user->id, 'category' => ['development', 'design', 'support', 'marketing'][$index % 4], 'priority' => ['low', 'normal', 'high', 'urgent'][$index % 4], 'status' => $index % 6 === 0 ? 'closed' : 'open', 'estimated_minutes' => [60, 120, 180, 240, 360][$index % 5], 'time_bookmarked' => $index < 7],
            );
        });

        $tasks = Task::whereIn('project_id', $projects->pluck('id'))->get();
        foreach (range(0, 34) as $daysAgo) {
            if (in_array(now()->subDays($daysAgo)->dayOfWeekIso, [6, 7], true) || $daysAgo % 3 === 0) {
                continue;
            }
            foreach (range(0, ($daysAgo % 2) + 1) as $slot) {
                $ticket = $tickets[($daysAgo + $slot) % $tickets->count()];
                $minutes = [30, 45, 60, 75, 90, 120][($daysAgo + $slot) % 6];
                $startedAt = Carbon::today()->subDays($daysAgo)->setTime(9 + ($slot * 2), 0);
                TimeEntry::updateOrCreate(
                    ['user_id' => $user->id, 'ticket_id' => $ticket->id, 'started_at' => $startedAt],
                    ['project_id' => $ticket->project_id, 'task_id' => $tasks[($daysAgo + $slot) % $tasks->count()]->id, 'description' => 'Worked on '.$ticket->title, 'ended_at' => $startedAt->copy()->addMinutes($minutes), 'booked_at' => $startedAt->copy()->addMinutes($minutes)],
                );
            }
        }
    }
}
