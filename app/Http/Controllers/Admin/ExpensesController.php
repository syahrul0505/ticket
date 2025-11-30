<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Expenses\AddExpensesRequest;
use App\Http\Requests\Admin\Expenses\UpdateExpensesRequest;
use App\Models\Expenses;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class ExpensesController extends Controller
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
        $data['page_title'] = 'Pengeluaran';
        $data['account_users'] = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $expenses = collect();

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $expenses = Expenses::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

            } else {
                $expenses = Expenses::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $expenses = Expenses::whereMonth('created_at', $monthPart)
                        ->orderBy('id', 'desc')
                        ->get();

        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $expenses = Expenses::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->get();
        }

        $data['expenses'] = $expenses->sum('amount');
        return view('admin.expenses.index', $data);
    }

    public function getExpenses(Request $request)
    {
        $data['account_users'] = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $expenses = collect();

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $expenses = Expenses::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

            } else {
                $expenses = Expenses::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $expenses = Expenses::whereMonth('created_at', $monthPart)
                        ->orderBy('id', 'desc')
                        ->get();

        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $expenses = Expenses::whereYear('created_at', $year)
                        ->orderBy('id', 'desc')
                        ->get();
        }

        if ($request->ajax()) {
            return DataTables::of($expenses)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning expenses-edit-table" data-bs-target="#tabs-'.$row->id.'-edit-expenses">Edit</button>';
                $btn = $btn . ' <button type="button" class="btn btn-sm btn-danger expenses-delete-table"  data-bs-target="#tabs-'.$row->id.'-delete-expenses">Delete</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function getModalAdd()
    {
        $data['account_users'] = User::get();

        return View::make('admin.expenses.modal-add',$data);
    }

    public function store(AddExpensesRequest $request)
    {
        $dataExpenses = $request->validated();
        try {
            $expenses                = new Expenses();
            $expenses->type          = $dataExpenses['type'];
            $expenses->amount        = (int) str_replace('.', '', $dataExpenses['amount']);
            $expenses->date          = $dataExpenses['date'];
            $expenses->description   = $dataExpenses['description'];
            $expenses->user_id       = Auth::user()->id;

            $expenses->save();

            $request->session()->flash('success', "Create data Expenses successfully!");
            return redirect(route('expenses.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to create data Expenses!");
            return redirect(route('expenses.index'));
        }
    }

    public function getModalEdit($expensesId)
    {
        $expenses = Expenses::findOrFail($expensesId);
        return View::make('admin.expenses.modal-edit')->with('expenses', $expenses);
    }


    public function update(UpdateExpensesRequest $request, $expensesId)
    {
        $dataExpenses = $request->validated();

        try {
            $expenses                = Expenses::find($expensesId);
            $expenses->type          = $dataExpenses['type'];
            $expenses->amount        = (int) str_replace('.', '', $dataExpenses['amount']);
            $expenses->date          = $dataExpenses['date'];
            $expenses->description   = $dataExpenses['description'];
            $expenses->user_id       = Auth::user()->id;

            $expenses->save();

            $request->session()->flash('success', "Update data Expenses successfully!");
            return redirect(route('expenses.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to update data Expenses!");
            return redirect(route('expenses.index'));
        }
    }

    public function getModalDelete($expensesId)
    {
        $expenses = Expenses::findOrFail($expensesId);
        return View::make('admin.expenses.modal-delete')->with('expenses', $expenses);
    }

    public function destroy(Request $request, $expensesId)
    {
        try {
            $expenses = Expenses::findOrFail($expensesId);
            $expenses->delete();

            $request->session()->flash('success', "Delete data Expenses successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Expenses not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to delete data Expenses!");
        }

        return redirect(route('expenses.index'));
    }
}
