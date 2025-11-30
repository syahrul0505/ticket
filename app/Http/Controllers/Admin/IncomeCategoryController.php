<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Income_category\AddIncomeCategoryRequest;
use App\Http\Requests\Admin\Income_category\UpdtaeIncomeCategoryRequest;
use App\Models\IncomeCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class IncomeCategoryController extends Controller
{
    function __construct()
    {
        // $this->middleware('permission:tag-list', ['only' => ['index', 'getTags']]);
        // $this->middleware('permission:tag-create', ['only' => ['getModalAdd','store']]);
        // $this->middleware('permission:tag-edit', ['only' => ['getModalEdit','update']]);
        // $this->middleware('permission:tag-delete', ['only' => ['getModalDelete','destroy']]);
    }

    public function index()
    {
        $data['page_title'] = 'Pemasukan Kategori';
        return view('admin.income-category.index', $data);
    }

    public function getIncomeCategory(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(IncomeCategory::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-warning income-category-edit-table" data-bs-target="#tabs-'.$row->id.'-edit-income-category">Edit</button>';
                $btn = $btn . ' <button type="button" class="btn btn-sm btn-danger income-category-delete-table"  data-bs-target="#tabs-'.$row->id.'-delete-income-category">Delete</button>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function getModalAdd()
    {
        return View::make('admin.income-category.modal-add');
    }

    public function store(AddIncomeCategoryRequest $request)
    {
        $dataIncomeCategory = $request->validated();
        try {
            $incomeCategory                = new IncomeCategory();
            $incomeCategory->name          = $dataIncomeCategory['name'];
            $incomeCategory->amount        = (int) str_replace('.', '', $dataIncomeCategory['amount']);

            $incomeCategory->save();

            $request->session()->flash('success', "Create data Income Category successfully!");
            return redirect(route('income-category.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to create data Income Category!");
            return redirect(route('income-category.index'));
        }
    }

    public function getModalEdit($incomeCategoryId)
    {
        $incomeCategory = IncomeCategory::findOrFail($incomeCategoryId);
        return View::make('admin.income-category.modal-edit')->with('income_category', $incomeCategory);
    }


    public function update(UpdtaeIncomeCategoryRequest $request, $incomeCategory)
    {
        $dataIncomeCategory = $request->validated();

        try {
            $incomeCategory                = IncomeCategory::find($incomeCategory);
            $incomeCategory->name          = $dataIncomeCategory['name'];
            $incomeCategory->amount        = (int) str_replace('.', '', $dataIncomeCategory['amount']);

            $incomeCategory->save();

            $request->session()->flash('success', "Update data Income Category successfully!");
            return redirect(route('income-category.index'));
        } catch (\Throwable $th) {
            $request->session()->flash('failed', "Failed to update data Income Category!");
            return redirect(route('income-category.index'));
        }
    }

    public function getModalDelete($incomeCategory)
    {
        $income_category = IncomeCategory::findOrFail($incomeCategory);
        return View::make('admin.income-category.modal-delete')->with('income_category', $income_category);
    }

    public function destroy(Request $request, $incomeCategory)
    {
        try {
            $incomeCategory = IncomeCategory::findOrFail($incomeCategory);
            $incomeCategory->delete();

            $request->session()->flash('success', "Delete data Income Category successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Income Category not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to delete data Income Category!");
        }

        return redirect(route('income-category.index'));
    }
}
