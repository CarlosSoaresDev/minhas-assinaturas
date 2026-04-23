<x-layouts::app :title="__('Dashboard')">
    @if(auth()->user()->hasRole('admin') && session('admin_mode', true))
        <livewire:dashboard.admin-dashboard />
    @else
        <livewire:dashboard.user-dashboard />
    @endif
</x-layouts::app>
