<?php

namespace App\Exports\Sheets;

use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class TopCustomersSheet implements FromArray, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Top Customers';
    }

    public function array(): array
    {
        $start = $this->startDate->copy()->startOfDay();
        $end = $this->endDate->copy()->endOfDay();

        $customers = Order::join('customers', 'orders.customerID', '=', 'customers.customerID')
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.order_status', 'completed')
            ->selectRaw('users.id, users.name, users.last_name, SUM(orders.final_amount) as total_spent')
            ->groupBy('users.id', 'users.name', 'users.last_name')
            ->orderByDesc('total_spent')
            ->take(10)
            ->get();

        $data = [['Customer', 'Total Spent']];

        foreach ($customers as $user) {
            $data[] = [
                $user->name . ' ' . $user->last_name,
                '₱' . number_format($user->total_spent, 2)
            ];
        }

        return $data;
    }
}
