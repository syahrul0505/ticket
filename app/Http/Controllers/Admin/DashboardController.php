<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $type = $request->input('type', 'day');
        $cashierName = $request->input('user_id', 'All');
        $date = $request->input('start_date', date('Y-m-d'));
    
        $ticket = collect();
    
        if ($type == 'day') {
            if ($cashierName == 'All') {
                $ticket = Ticket::whereDate('created_at', $date)
                        ->orderBy('id', 'desc')
                        ->get();
            } else {
                $ticket = Ticket::where('assigned_to', $cashierName)
                        ->whereDate('created_at', $date)
                        ->orderBy('id', 'desc')
                        ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month));
            $ticket = Ticket::whereMonth('created_at', $monthPart)
                    ->when($cashierName != 'All', function ($query) use ($cashierName) {
                        return $query->where('assigned_to', $cashierName);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $ticket = Ticket::whereYear('created_at', $year)
                    ->when($cashierName != 'All', function ($query) use ($cashierName) {
                        return $query->where('assigned_to', $cashierName);
                    })
                    ->orderBy('id', 'desc')
                    ->get();
        }
    
        $hourlyOrders = $ticket->groupBy(function($order) {
            return Carbon::parse($order->created_at)->format('H');
        })->map(function($hour) {
            return $hour->count();
        });
    
        $data['hourlyOrders'] = $hourlyOrders;
    
        return view('admin.dashboard.index', $data);
    }
    
    
}
