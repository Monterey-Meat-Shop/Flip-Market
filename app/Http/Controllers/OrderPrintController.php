<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderPrintController extends Controller
{
    public function print(Order $order)
    {
        // optional: $this->authorize('view', $order);
        return view('filament.transactions.print', compact('order'));
    }
}