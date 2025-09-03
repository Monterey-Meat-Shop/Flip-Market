
<x-filament::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @livewire(\App\Filament\Resources\ReportResource\Widgets\ReportStats::class)
            @livewire(\App\Filament\Resources\ReportResource\Widgets\OrdersStats::class)
            @livewire(\App\Filament\Resources\ReportResource\Widgets\PaymentsStats::class)
            @livewire(\App\Filament\Resources\ReportResource\Widgets\OrdersRealtimeStats::class)
        </div>
        {{-- no table / no recent reports widget here --}}
    </div>
</x-filament::page>