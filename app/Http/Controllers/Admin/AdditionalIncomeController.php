<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Additional_income\AddAdditionalIncomeRequest;
use App\Http\Requests\Admin\Additional_income\UpdateAdditionalIncomeRequest;
use App\Models\AdditionalIncome;
use App\Models\IncomeCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class AdditionalIncomeController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:tag-list', ['only' => ['index', 'getTags']]);
        // $this->middleware('permission:tag-create', ['only' => ['getModalAdd','store']]);
        // $this->middleware('permission:tag-edit', ['only' => ['getModalEdit','update']]);
        // $this->middleware('permission:tag-delete', ['only' => ['getModalDelete','destroy']]);
    }

    public function index(Request $request)
    {
        $data['page_title'] = 'Pemasukan Kategori';

        $user = Auth::user(); // Get the currently authenticated user
        
        // Check if the logged-in user has the 'super-admin' role
        $isSuperAdmin = in_array('super-admin', $user->getRoleNames()->toArray());

        // If the user is a super-admin, fetch all users, otherwise filter by user_id
        if ($isSuperAdmin) {
            // If user is super-admin, you can show all users
            $data['account_users'] = User::orderBy('fullname', 'asc')->get();
        } else {
            // If the user is not a super-admin, filter users based on the user_id
            $data['account_users'] = User::where('id', $user->id)->get(); // Filter by user_id
        }

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $additionalIncome = AdditionalIncome::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $additionalIncome = AdditionalIncome::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $additionalIncome = AdditionalIncome::whereMonth('created_at', $monthPart)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $additionalIncome = AdditionalIncome::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        }

        $data['total'] = $additionalIncome->sum('amount');
        return view('admin.additional-income.index', $data);
    }

    public function getAdditionalIncome(Request $request)
    {

        $page_title = 'Report Sales Gross Profit';
        $account_users = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $orders = collect();

        if ($type == 'day') {
            if ($user == 'All') {
                $additionalIncome = AdditionalIncome::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $additionalIncome = AdditionalIncome::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $additionalIncome = AdditionalIncome::whereMonth('created_at', $monthPart)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $additionalIncome = AdditionalIncome::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        }

        if ($request->ajax()) {
            return DataTables::of($additionalIncome)
            ->addIndexColumn()
            ->addColumn('user_name', function ($row) {
                return $row->user ? $row->user->fullname : '-'; // Pastikan tidak null
            })
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning additional-income-edit-table" data-bs-target="#tabs-'.$row->id.'-edit-additional-income">Edit</button>';
                $btn = $btn . ' <button type="button" class="btn btn-sm btn-danger additional-income-delete-table"  data-bs-target="#tabs-'.$row->id.'-delete-additional-income">Delete</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }

    }

    public function getModalAdd()
    {
        $data['income_categories'] = IncomeCategory::orderBy('name','asc')->get();
        return View::make('admin.additional-income.modal-add',$data);
    }

    public function store(AddAdditionalIncomeRequest $request)
    {
        $dataAdditionalIncome = $request->validated();
        
        try {
            $incomeCategory = IncomeCategory::where('name',$dataAdditionalIncome['category'])->first();

            $additionalIncome                = new AdditionalIncome();
            $additionalIncome->category      = $dataAdditionalIncome['category'];
            $additionalIncome->amount        = $incomeCategory->amount * $dataAdditionalIncome['qty'];
            $additionalIncome->qty           = $dataAdditionalIncome['qty'];
            $additionalIncome->income_date   = $dataAdditionalIncome['income_date'];
            $additionalIncome->description   = $dataAdditionalIncome['description'];
            $additionalIncome->user_id       = Auth::user()->id;

            $additionalIncome->save();

            $request->session()->flash('success', "Create data Pengasilan Tambahan successfully!");
            return redirect(route('additional-income.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to create data Pengasilan Tambahan!");
            return redirect(route('additional-income.index'));
        }
    }

    public function getModalEdit($additionalIncomeId)
    {
        $data['income_categories'] = IncomeCategory::orderBy('name', 'asc')->get();
        $data['additional_income'] = AdditionalIncome::findOrFail($additionalIncomeId);

        return View::make('admin.additional-income.modal-edit', $data);
    }


    public function update(UpdateAdditionalIncomeRequest $request, $additionalIncomeId)
    {
        $dataAdditionalIncome = $request->validated();

        try {
            $incomeCategory = IncomeCategory::where('name',$dataAdditionalIncome['category'])->first();

            $additionalIncome                = AdditionalIncome::find($additionalIncomeId);
            $additionalIncome->category      = $dataAdditionalIncome['category'];
            $additionalIncome->amount        = $incomeCategory->amount * $dataAdditionalIncome['qty'];
            $additionalIncome->qty           = $dataAdditionalIncome['qty'];
            $additionalIncome->income_date   = $dataAdditionalIncome['income_date'];
            $additionalIncome->description   = $dataAdditionalIncome['description'];
            $additionalIncome->user_id       = Auth::user()->id;

            $additionalIncome->save();

            $request->session()->flash('success', "Update data Penghasilan Tambahan successfully!");
            return redirect(route('additional-income.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to update data Penghasilan Tambahan!");
            return redirect(route('additional-income.index'));
        }
    }

    public function getModalDelete($additionalIncomeId)
    {
        $additionalIncome = AdditionalIncome::findOrFail($additionalIncomeId);
        return View::make('admin.additional-income.modal-delete')->with('additional_income', $additionalIncome);
    }

    public function destroy(Request $request, $additionalIncomeId)
    {
        try {
            $additionalIncome = AdditionalIncome::findOrFail($additionalIncomeId);
            $additionalIncome->delete();

            $request->session()->flash('success', "Delete data Penghasilan Tambahan successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Penghasilan Tambahan not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to delete data Penghasilan Tambahan!");
        }

        return redirect(route('additional-income.index'));
    }
}
