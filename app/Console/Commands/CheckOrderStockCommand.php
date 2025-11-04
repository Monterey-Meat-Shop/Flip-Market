<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CheckOrderStockCommand extends Command
{
    protected $signature = 'orders:check-stock';
    protected $description = 'Check pending orders for insufficient stock and auto-reject them';

    public function handle()
    {
        $this->info('Checking pending orders for insufficient stock...');

        // Get all pending orders
        $pendingOrders = Order::where('order_status', 'pending')
            ->with(['orderItems.productVariant', 'orderItems.product'])
            ->get();

        $rejectedCount = 0;

        foreach ($pendingOrders as $order) {
            if ($order->autoRejectIfOutOfStock()) {
                $rejectedCount++;
                $this->warn("Order #{$order->orderID} auto-rejected due to insufficient stock.");
            }
        }

        if ($rejectedCount > 0) {
            $this->info(" {$rejectedCount} order(s) auto-rejected and restocked.");
            Log::info("Auto-rejected {$rejectedCount} orders due to insufficient stock.");
        } else {
            $this->info(' No orders needed to be rejected.');
        }

        return 0;
    }
}