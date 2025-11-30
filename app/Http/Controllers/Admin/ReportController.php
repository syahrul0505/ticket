<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdditionalIncome;
use App\Models\Attendance;
use App\Models\Expenses;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function reportGross(Request $request){
        $data ['page_title'] = 'Report Sewa Penjualan';
        $data['account_users'] = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();
        $cashierName = $request->user_id;

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $order = Order::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $additionalIncome = AdditionalIncome::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $expenses = Expenses::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $attendance = Attendance::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $order = Order::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $additionalIncome = AdditionalIncome::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $expenses = Expenses::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $attendance = Attendance::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('Y-m'));
            $year = date('Y', strtotime($month));
            $monthPart = date('m', strtotime($month));

            $order = Order::whereMonth('created_at', $monthPart)
                ->whereYear('created_at', $year) 
                ->when($cashierName != 'All', function ($query) use ($cashierName) {
                    return $query->where('cashier_name', $cashierName);
                })
                ->where('payment_status', 'Paid')
                ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                ->orderBy('id', 'desc')
                ->get();

            $additionalIncome = AdditionalIncome::whereMonth('created_at', $monthPart)
                ->whereYear('created_at', $year) 
                ->when($user != 'All', function ($query) use ($user) {
                    return $query->where('user_id', $user);
                })
                ->orderBy('id', 'desc')
                ->get();

            $expenses = Expenses::whereMonth('created_at', $monthPart)
                ->whereYear('created_at', $year) 
                ->when($user != 'All', function ($query) use ($user) {
                    return $query->where('user_id', $user);
                })
                ->orderBy('id', 'desc')
                ->get();

            $attendance = Attendance::whereMonth('created_at', $monthPart)
                ->whereYear('created_at', $year) 
                ->when($user != 'All', function ($query) use ($user) {
                    return $query->where('user_id', $user);
                })
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($type == 'yearly') {
                    $year = $request->input('year', date('Y'));
                    $order = Order::whereYear('created_at', $year)
                                ->when($cashierName != 'All', function ($query) use ($cashierName) {
                                    return $query->where('cashier_name', $cashierName);
                                })
                                ->where('payment_status', 'Paid')
                                ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                                ->orderBy('id', 'desc')
                                ->get();
                                
            $additionalIncome = AdditionalIncome::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
            $expenses = Expenses::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();

            $attendance = Attendance::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        }

        $additionalIncomes = $additionalIncome->sum('amount');
        $expense = $expenses->sum('amount');
        $attendances = $attendance->sum('total_salary');
        $data['total'] = $order->sum('total')- $additionalIncomes - $expense - $attendances;
        $data['total_gross'] = $order->sum('total');

        return view('admin.report.sales.gross-profit',$data);
    }

    public function getReportGross(Request $request)
    {
        $page_title = 'Report Sales Gross Profit';
        $account_users = User::get();

        $type = $request->input('type', 'day');
        $cashierName = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($cashierName == 'All') {
                $orders = Order::where('payment_status', 'Paid')
                            ->whereDate('created_at', $date)
                            ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $orders = Order::where('cashier_name', $cashierName)
                            ->where('payment_status', 'Paid')
                            ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('Y-m')); 
            $year = date('Y', strtotime($month)); 
            $monthPart = date('m', strtotime($month)); 

            $orders = Order::whereMonth('created_at', $monthPart)
                            ->whereYear('created_at', $year) // + pake tahun
                            ->when($cashierName != 'All', function ($query) use ($cashierName) {
                                return $query->where('user_id', $cashierName);
                            })
                            ->where('payment_status', 'Paid')
                            ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                            ->orderBy('id', 'desc')
                            ->get();
        } elseif ($type == 'yearly') {
                    $year = $request->input('year', date('Y'));
                    $orders = Order::whereYear('created_at', $year)
                                ->when($cashierName != 'All', function ($query) use ($cashierName) {
                                    return $query->where('cashier_name', $cashierName);
                                })
                                ->where('payment_status', 'Paid')
                                ->whereIn('status_product', ['Sewa', 'Sewa Paid'])
                                ->orderBy('id', 'desc')
                                ->get();
                }

        if (auth()->user()->can('update-order')) {
            $datatables = DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('order_products', function($row) {
                    return $row->orderProducts->map(function($product) {
                        $addons = $product->orderProductAddons->map(function($addon) {
                            return $addon->name;
                        })->implode(', ');
        
                        return $product->name . ' (' . $addons . ')';
                    })->implode('<br>');
                })
                ->addColumn('action', function($row) {
                    return '<button type="button" class="btn btn-sm btn-secondary orders-update-table" data-bs-target="#tabs-'.$row->id.'-update-order">Update</button>';
                })
                ->rawColumns(['order_products', 'action'])
                ->make(true);
        } else {
            $datatables = DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('order_products', function($row) {
                    return $row->orderProducts->map(function($product) {
                        $addons = $product->orderProductAddons->map(function($addon) {
                            return $addon->name;
                        })->implode(', ');
        
                        return $product->name . ' (' . $addons . ')';
                    })->implode('<br>');
                })
                ->rawColumns(['order_products'])
                ->make(true);
        }
        return $datatables;
    }


    public function reportProduct(Request $request){
        $data ['page_title'] = 'Report Produk';
        $data['account_users'] = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $order = Order::whereDate('created_at', $date)
                            ->where('status_product','Product')
                            ->orderBy('id', 'desc')
                            ->get();

            } else {
                $order = Order::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->where('status_product','Product')
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('Y-m'));
            $year = date('Y', strtotime($month));
            $monthPart = date('m', strtotime($month));
            $order = Order::whereMonth('created_at', $monthPart)
                            ->whereYear('created_at', $year) //tambahan pake tahun
                            ->when($user != 'All', function ($query) use ($user) {
                                return $query->where('cashier_name', $user);
                            })
                            ->where('status_product','Product')
                            ->where('payment_status', 'Paid')
                            ->orderBy('id', 'desc')
                            ->get();    

        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $order = Order::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->where('status_product','Product')
                        ->orderBy('id', 'desc')
                        ->get();
                        
        }

        $groupedData = [];

        // Iterate through the $stok array and group by payment method
        foreach ($orders as $order) {
            $paymentMethod = $order->payment_method ?? 'Unknown';

            // If the payment method is not already in the groupedData array, initialize it
            if (!isset($groupedData[$paymentMethod])) {
                $groupedData[$paymentMethod] = [
                    'payment_method' => $paymentMethod,
                    'total' => 0,
                    'quantity_method' => 0,
                ];
            }

            // Update the total price and count for the current payment method
            $groupedData[$paymentMethod]['total'] += $order->total ?? 0;
            $groupedData[$paymentMethod]['quantity_method']++;
        }

        $groupedData = array_values($groupedData);

        $totalPriceSum = $order->sum('total');

        $modal = OrderProduct::whereIn('order_id', $order->pluck('id'))
            ->selectRaw('sum(cost_price * qty) as total_cost')
            ->value('total_cost');

        $data['total_price'] = $totalPriceSum;
        $data['nett_sales'] = (int)$totalPriceSum - (int)$modal;

        return view('admin.report.sales.product',$data);
    }

    public function getModalUpdate($orderId)
    {
        $order = Order::findOrfail($orderId);
        return View::make('admin.report.sales.modal-update')->with('order', $order);
    }

    public function update(Request $request, $supplierId)
    {
        try {
            $order = Order::findOrFail($supplierId);
            $order->payment_method = $request->payment_method;
            $order->save();

            $request->session()->flash('success', "Updtae data Order successfully!");
            return redirect()->back();
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to update data tag!");
            return redirect()->back();
        }

        return redirect(route('suppliers.index'));
    }

    public function getReportProduct(Request $request)
    {
        $page_title = 'Report Sales Gross Profit';
        $account_users = User::get();

        $type = $request->input('type', 'day');
        $cashierName = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($cashierName == 'All') {
                $orders = Order::where('payment_status', 'Paid')
                            ->whereDate('created_at', $date)
                            ->where('status_product','Product')
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $orders = Order::where('cashier_name', $cashierName)
                            ->where('payment_status', 'Paid')
                            ->where('status_product','Product')
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
           $month = $request->input('month', date('Y-m'));
            $year = date('Y', strtotime($month));
            $monthPart = date('m', strtotime($month));
            $orders = Order::whereMonth('created_at', $monthPart)
                            ->whereYear('created_at', $year) //tambahan pake tahun
                            ->when($cashierName != 'All', function ($query) use ($cashierName) {
                                return $query->where('cashier_name', $cashierName);
                            })
                            ->where('status_product','Product')
                            ->where('payment_status', 'Paid')
                            ->orderBy('id', 'desc')
                            ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $orders = Order::whereYear('created_at', $year)
                        ->when($cashierName != 'All', function ($query) use ($cashierName) {
                            return $query->where('cashier_name', $cashierName);
                        })
                        ->where('status_product','Product')
                        ->where('payment_status', 'Paid')
                        ->orderBy('id', 'desc')
                        ->get();
        }

        if ($request->ajax()) {
            // $query = Order::with(['orderProducts.orderProductAddons'])->select('orders.*');
            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('order_products', function($row) {
                    return $row->orderProducts->map(function($product) {
                        $addons = $product->orderProductAddons->map(function($addon) {
                            return $addon->name;
                        })->implode(', ');

                        return $product->name . ' (' . $addons . ')';
                    })->implode('<br>');
                })
                ->addColumn('action', function($row) {
                    return '<a href="#" class="btn btn-sm btn-primary">View</a>';
                })
                ->rawColumns(['order_products', 'action'])
                ->make(true);
        }
    }

    public function paymentMethod(){
        $data ['page_title'] = 'Report Sales Gross Profit';
        $data['account_users'] = User::get();

        return view('admin.report.sales.payment-method',$data);
    }

    public function getReportPayment(Request $request)
    {
        $type = $request->input('type', 'day');
        $cashierName = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($cashierName == 'All') {
                $orders = Order::where('payment_status', 'Paid')
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $orders = Order::where('payment_status', 'Paid')
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $orders = Order::whereMonth('created_at', $monthPart)
                        ->when($cashierName != 'All', function ($query) use ($cashierName) {
                            return $query;
                        })
                        ->where('payment_status', 'Paid')
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $orders = Order::whereYear('created_at', $year)
                        ->when($cashierName != 'All', function ($query) use ($cashierName) {
                            return $query;
                        })
                        ->where('payment_status', 'Paid')
                        ->orderBy('id', 'desc')
                        ->get();
        }

        // Define an array to store the grouped data
        $groupedData = [];

        // Iterate through the $stok array and group by payment method
        foreach ($orders as $order) {
            $paymentMethod = $order->payment_method ?? 'Unknown';

            // If the payment method is not already in the groupedData array, initialize it
            if (!isset($groupedData[$paymentMethod])) {
                $groupedData[$paymentMethod] = [
                    'payment_method' => $paymentMethod,
                    'total' => 0,
                    'quantity_method' => 0,
                ];
            }

            // Update the total price and count for the current payment method
            $groupedData[$paymentMethod]['total'] += $order->total ?? 0;
            $groupedData[$paymentMethod]['quantity_method']++;
        }

        $groupedData = array_values($groupedData);

        if ($request->ajax()) {
            return DataTables::of($groupedData)
                ->addIndexColumn()
                ->make(true);
        }
    }

    public function reportRefund(Request $request){
        $data ['page_title'] = 'Report Pengembalian';
        $data['account_users'] = User::get();

        return view('admin.report.sales.refund',$data);
    }

    public function getReportRefund(Request $request)
    {
        $page_title = 'Report Sales Gross Profit';
        $account_users = User::get();

        $type = $request->input('type', 'day');
        $cashierName = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($cashierName == 'All') {
                $orders = Order::where('payment_status', 'Unpaid')
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $orders = Order::where('cashier_name', $cashierName)
                            ->where('payment_status', 'Unpaid')
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $orders = Order::whereMonth('created_at', $monthPart)
                        ->when($cashierName != 'All', function ($query) use ($cashierName) {
                            return $query->where('cashier_name', $cashierName);
                        })
                        ->where('payment_status', 'Unpaid')
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $orders = Order::whereYear('created_at', $year)
                        ->when($cashierName != 'All', function ($query) use ($cashierName) {
                            return $query->where('cashier_name', $cashierName);
                        })
                        ->where('payment_status', 'Unpaid')
                        ->orderBy('id', 'desc')
                        ->get();
        }

        if ($request->ajax()) {
            // $query = Order::with(['orderProducts.orderProductAddons'])->select('orders.*');
            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('order_products', function($row) {
                    return $row->orderProducts->map(function($product) {
                        $addons = $product->orderProductAddons->map(function($addon) {
                            return $addon->name;
                        })->implode(', ');

                        return $product->name . ' (' . $addons . ')';
                    })->implode('<br>');
                })
                ->addColumn('action', function($row) {
                    return '<a href="#" class="btn btn-sm btn-primary">View</a>';
                })
                ->rawColumns(['order_products', 'action'])
                ->make(true);
        }
    }
}
