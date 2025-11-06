<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockOrder;
use App\Models\User;
use Filament\Notifications\Notification;

class NotifyApproachingStockOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:notify-approaching';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for approaching and overdue stock orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for approaching and overdue stock orders...');

        // Get all admin users
        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found.');
            return;
        }

        // Check overdue orders
        $overdueOrders = StockOrder::query()
            ->whereIn('status', ['pending', 'partial'])
            ->where('estimated_delivery_date', '<', now())
            ->with(['product', 'productVariant'])
            ->get();

        if ($overdueOrders->isNotEmpty()) {
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Overdue Stock Orders!')
                    ->body("You have {$overdueOrders->count()} overdue stock order(s). Please review them immediately.")
                    ->danger()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.stock-orders.index'))
                    ])
                    ->sendToDatabase($admin);
            }

            $this->info("Sent {$overdueOrders->count()} overdue notifications.");
        }

        // Check orders arriving today
        $todayOrders = StockOrder::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('estimated_delivery_date', now())
            ->with(['product', 'productVariant'])
            ->get();

        if ($todayOrders->isNotEmpty()) {
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Stock Arriving Today!')
                    ->body("You have {$todayOrders->count()} stock order(s) scheduled for delivery today.")
                    ->warning()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.stock-orders.index'))
                    ])
                    ->sendToDatabase($admin);
            }

            $this->info("Sent {$todayOrders->count()} today delivery notifications.");
        }

        // Check orders arriving within 3 days
        $approachingOrders = StockOrder::query()
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('estimated_delivery_date', [now()->addDay(), now()->addDays(3)])
            ->with(['product', 'productVariant'])
            ->get();

        if ($approachingOrders->isNotEmpty()) {
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Upcoming Stock Deliveries')
                    ->body("You have {$approachingOrders->count()} stock order(s) arriving within the next 3 days.")
                    ->info()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view')
                            ->button()
                            ->url(route('filament.admin.resources.stock-orders.index'))
                    ])
                    ->sendToDatabase($admin);
            }

            $this->info("Sent {$approachingOrders->count()} approaching delivery notifications.");
        }

        if ($overdueOrders->isEmpty() && $todayOrders->isEmpty() && $approachingOrders->isEmpty()) {
            $this->info('No notifications needed at this time.');
        }

        $this->info('Notification check complete!');
        return Command::SUCCESS;
    }
}