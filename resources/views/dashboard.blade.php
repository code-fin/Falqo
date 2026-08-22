@php($browserTitle = match($section ?? 'overview') { 'projects', 'project' => 'Projects', 'tickets', 'ticket' => 'Tickets', 'tasks' => 'Tasks', 'calendar' => 'Calendar', 'time' => 'Time tracker', 'customers', 'customer' => 'Customers', default => 'Dashboard' })
<x-layouts::app :title="$browserTitle">
    <livewire:workspace :section="$section ?? 'overview'" :show-id="isset($showId) ? (int) $showId : null" />
</x-layouts::app>
