<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Customer\AddCustomerRequest;
use App\Http\Requests\Admin\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;


class CustomerController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:customer-list', ['only' => ['index', 'getCustomers']]);
        $this->middleware('permission:customer-create', ['only' => ['getModalAdd','store']]);
        $this->middleware('permission:customer-edit', ['only' => ['getModalEdit','update']]);
        $this->middleware('permission:customer-delete', ['only' => ['getModalDelete','destroy']]);
    }

    public function index()
    {
        $data['page_title'] = 'Pelanggan';
        return view('admin.customer.index', $data);
    }

    public function getCustomers(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Customer::query())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $showButton = sprintf(
                        '<form action="%s" method="GET" style="display:inline;">
                            <input type="hidden" name="phone" value="%s">
                            <button type="submit" class="btn btn-sm btn-info">Lihat</button>
                        </form>',
                        route('customers.show-detail', ['customerId' => $row->id]), // Use customerId
                        htmlspecialchars($row->phone) // Escape special characters
                    );
                    $editButton = '<button type="button" class="btn btn-sm btn-warning customers-edit-table" data-bs-target="#tabs-'.$row->id.'-edit-customer">Edit</button>';
                    $deleteButton = '<button type="button" class="btn btn-sm btn-danger customers-delete-table" data-bs-target="#tabs-'.$row->id.'-delete-customer">Delete</button>';
                    
                    // Concatenate all buttons together
                    $btn = $showButton . ' ' . $editButton . ' ' . $deleteButton;

                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function historyCustomers(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::where('customer_phone', $request->phone)
                ->orderBy('no_invoice', 'asc')
                ->get();

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('order_products', function ($row) {
                    return $row->orderProducts->map(function ($product) {
                        $addons = $product->orderProductAddons->pluck('name')->implode(', ');

                        return $product->name . ($addons ? ' (' . $addons . ')' : '');
                    })->implode('<br>');
                })
                ->addColumn('action', function ($row) {
                    if ($row->payment_status_midtrans !== 'Paid') {
                        return '<button type="button" class="btn btn-sm btn-warning customers-reset-table" 
                                data-bs-target="#tabs-' . $row->id . '-reset-customer">Update</button>';
                    }
                    return ''; // Return empty string if status is 'Paid'
                })
                ->rawColumns(['action', 'order_products'])
                ->make(true);
        }
    }



    public function getModalAdd()
    {
        $code = $this->generateCode();
        return View::make('admin.customer.modal-add')->with([
            'code' => $code
        ]);
    }

    public function generateCode()
    {
        $code = Customer::latest()->first();
        if ($code) {
            $code = $code->code;
            $code = substr($code, 4);
            $code = intval($code) + 1;
            $code = 'CUST' . str_pad($code, 5, '0', STR_PAD_LEFT);
        } else {
            $code = 'CUST00001';
        }
        return $code;
    }

    public function store(AddCustomerRequest $request)
    {
        $dataCustomer = $request->validated();
        try {
            $customer = new Customer();
            $customer->code               = $dataCustomer['code'];
            $customer->name               = $dataCustomer['name'];
            $customer->phone              = $dataCustomer['phone'];
            $customer->gender             = $dataCustomer['gender'];
            $customer->instagram          = $dataCustomer['instagram'];
            $customer->address            = $dataCustomer['address'];
            $customer->total_transaction  = 0;

            $customer->save();

            $request->session()->flash('success', "Create data customer successfully!");
            return redirect(route('customers.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to create data customer!");
            return redirect(route('customers.index'));
        }
    }

    public function getModalEdit($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        return View::make('admin.customer.modal-edit')->with(
        [
            'customer' => $customer
        ]);
    }

    public function show($customerId)
    {
        $data['page_title'] = 'Customer Show';

        return view('admin.customer.show', $data);
    }

    public function getModalReset($orderId)
    {
        $order = Order::findOrFail($orderId);
        return View::make('admin.customer.modal-reset')->with('order', $order);
    }


    public function update(UpdateCustomerRequest $request, $customerId)
    {
        $dataCustomer = $request->validated();
        try {
            $customer = Customer::find($customerId);

            // Check if customr$customer doesn't exists
            if (!$customer) {
                $request->session()->flash('failed', "Customer not found!");
                return redirect()->back();
            }

            $customer->code               = $dataCustomer['code'];
            $customer->name               = $dataCustomer['name'];
            $customer->phone              = $dataCustomer['phone'];
            $customer->gender             = $dataCustomer['gender'];
            $customer->instagram          = $dataCustomer['instagram'];
            $customer->address            = $dataCustomer['address'];

            $customer->save();

            $request->session()->flash('success', "Update data customer successfully!");
            return redirect(route('customers.index'));
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $request->session()->flash('failed', "Failed to update data customer!");
            return redirect(route('customers.index'));
        }
    }

    public function getModalDelete($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        return View::make('admin.customer.modal-delete')->with('customer', $customer);
    }

    public function reset(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $order->payment_status_midtrans = 'Paid';
            $order->cash = $request->cash ?? 0;
            
            if ($request->payment_method == 'Cash' && $request->cash != null) {
                $kembalian = $request->cash - $order->total ;
            } 
            $order->kembalian = $kembalian ?? 0;

            $dendaBarangRusak = $request->denda_barang_rusak ?? 0;
            $subtotal       = $order->subtotal + $order->pb01 + $order->service;
            $total          = ($subtotal * ($order->sewa + $request->denda));
            $order->total   =  $total + $dendaBarangRusak;
            $order->sewa    = $order->sewa + $request->denda;
            $order->denda   = $request->denda;

            $order->save();

            // Find the associated OrderProduct records
            $order_products = OrderProduct::where('order_id', $order->id)->get();
    
            foreach ($order_products as $order_product) {
                $products = Product::where('name', $order_product->name)->get();
                
                foreach ($products as $product) {
                    $product->current_stock += $order_product->qty;
                    $product->save();
                }
            }

            $customer = Customer::where('name', $order->customer_name)->first();
            
            if ($customer) {
                $customer->status = 'Selesai';
                $customer->save();
            }

            $request->session()->flash('success', "Update data customer successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Order not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to update customer data!" , $e->getMessage());
        }

        return redirect(route('customers.index'));
    }


    public function destroy(Request $request, $customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $customer->delete();

            $request->session()->flash('success', "Delete data customer successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Customer not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to delete data customer!");
        }

        return redirect(route('customers.index'));
    }
}
