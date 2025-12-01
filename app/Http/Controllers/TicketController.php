<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueWhatsapp;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\DataTables;

class TicketController extends Controller
{
     public function index()
    {
        $data['page_title'] = 'Ticket List';
        $data['account_users'] = User::get();

        return view('admin.ticket.index', $data);
    }

    public function getTicket(Request $request)
    {
        $data['account_users'] = User::get();

        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $tickets as an empty collection
        $tickets = collect();
        $cashierName = $request->user_id;

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $ticket = Ticket::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

            } else {
                $ticket = Ticket::where('assigned_to', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('Y-m'));
            $year = date('Y', strtotime($month));
            $monthPart = date('m', strtotime($month));

            $ticket = Ticket::whereMonth('created_at', $monthPart)
                ->whereYear('created_at', $year) 
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

        if ($request->ajax()) {
            return DataTables::of($ticket)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {

                $btn  = '<button type="button" class="btn btn-sm btn-warning tickets-edit-table" data-bs-target="#tabs-'.$row->id.'-edit-ticket">Edit</button>';

                if ($row->status != 'closed') {
                    $btn .= ' <button type="button" class="btn btn-sm btn-secondary tickets-show-table" data-bs-target="#tabs-'.$row->id.'-show-ticket">Comment</button>';
                }

                $btn .= ' <button type="button" class="btn btn-sm btn-danger tickets-delete-table" data-bs-target="#tabs-'.$row->id.'-delete-ticket">Delete</button>';

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    public function getModalAdd()
    {
        $code = $this->generateCode();
        $users = User::orderBy('fullname','asc')->get();
        return View::make('admin.ticket.modal-add')->with([
            'code' => $code,
            'users' => $users
        ]);
    }

    public function generateCode()
    {
        $code = User::latest()->first();
        if ($code) {
            $code = $code->code;
            $code = substr($code, 3);
            $code = intval($code) + 1;
            $code = 'TKT' . str_pad($code, 5, '0', STR_PAD_LEFT);
        } else {
            $code = 'TKT00001';
        }
        return $code;
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'code'              => 'required|string|unique:tickets,code',
            'title'             => 'required|string',
            'priority'          => 'required|in:low,medium,high',
            'user'              => 'required|string',
            'problem_category'  => 'nullable|string',
            'system_to_fix'     => 'nullable|string',
            'description'       => 'nullable|string',
        ]);


        $ticket = Ticket::create([
            'code'             => $data['code'],
            'title'            => $data['title'],
            'priority'         => $data['priority'],
            'problem_category' => $data['problem_category'] ?? null,
            'system_to_fix'    => $data['system_to_fix'] ?? null,
            'assigned_to'      => $data['user'] ?? null,
            'description'      => $data['description'] ?? null,
            'created_by'       => Auth::user()->fullname ?? '-',
        ]);

       
        $ticketComment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user'      => Auth::user()->fullname ?? '-',
            'phone'     => Auth::user()->phone ?? '-',
            'comment'   => 'Tiket dibuat',
            'action'    => 'created'
        ]);

       QueueWhatsapp::create([
            'phone_number' => $ticketComment->phone ?? null , 
            'message' => "Anda telah ditugaskan ke tiket *{$ticket->code}*\n".
                        "Judul: {$ticket->title}\n".
                        "Status : {$ticket->status}\n".
                        "Dari: {$ticket->assiged_to}\n".
                        "Ke: {$ticket->assigned_to}\n".
                        "Problem Category: {$ticket->problem_category}\n".
                        "Description: {$ticket->description}\n"

        ]);

        return redirect()->route('tickets.index', $ticket)->with('success', 'Ticket created');
    }

    public function getModalShow($id)
    {
        $data['ticket'] = Ticket::findOrFail($id);
        return view('admin.ticket.modal-show',$data);
    }

    public function getModalEdit($id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = User::orderBy('fullname','asc')->get();
        return View::make('admin.ticket.modal-edit')->with(
        [
            'ticket' => $ticket,
            'users' => $user
        ]);
    }


    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'title'             => 'required|string',
                'priority'          => 'required',
                'user'              => 'required|string',
                'problem_category'  => 'nullable|string',
                'system_to_fix'     => 'nullable|string',
                'description'       => 'nullable|string',
            ]);

            $ticket = Ticket::findOrFail($id);

            $oldUser = $ticket->assigned_to;

            $ticket->title             = $data['title'];
            $ticket->priority          = $data['priority'];
            $ticket->problem_category  = $data['problem_category'] ?? null;
            $ticket->system_to_fix     = $data['system_to_fix'] ?? null;
            $ticket->assigned_to       = $data['user'] ?? null;
            $ticket->description       = $data['description'] ?? null;
            $ticket->save();

            if ($oldUser !== $ticket->assigned_to) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user'      => Auth::user()->fullname ?? '-',
                    'comment'   => "Tiket dipindahkan dari {$oldUser} ke {$ticket->assigned_to}",
                    'action'    => 'reassigned',
                ]);
            }

            return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully!');
                
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect()->route('tickets.index')->with('failed', 'Failed to update ticket!');
        }
    }


    public function updateComment(Request $request, $ticketId)
    {
        $data = $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $ticket = Ticket::findOrFail($ticketId);
            $ticket->status = $request->input('status', $ticket->status);
            $ticket->save();

            $ticketComment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'user'      => Auth::user()->fullname ?? '-',
                'phone'     => Auth::user()->phone ?? '-',
                'comment'   => $data['comment'],
                'action'    => 'commented'
            ]);

            QueueWhatsapp::create([
                'phone_number' => $ticketComment->phone ?? null,
                'message' => "Anda telah ditugaskan ke tiket *{$ticket->code}*\n".
                            "Judul: {$ticket->title}\n".
                            "Status : {$ticket->status}\n".
                            "Dari: {$ticket->assiged_to}\n".
                            "Ke: {$ticket->assigned_to}\n".
                            "Problem Category: {$ticket->problem_category}\n".
                            "Description: {$ticket->description}\n"

            ]);

            $request->session()->flash('success', "Comment added successfully!");
        } catch (\Throwable $th) {
            dd($th->getMessage());
            $request->session()->flash('failed', "Failed to add comment!");
        }

        return redirect(route('tickets.index'));
    }

    public function getModalDelete($materialId)
    {
        $ticket = Ticket::findOrFail($materialId);
        return View::make('admin.ticket.modal-delete')->with('ticket', $ticket);
    }

    public function destroy(Request $request, $materialId)
    {
        try {
            $material = Ticket::findOrFail($materialId);
            $material->delete();

            $request->session()->flash('success', "Delete data material successfully!");
        } catch (ModelNotFoundException $e) {
            $request->session()->flash('failed', "Material not found!");
        } catch (QueryException $e) {
            $request->session()->flash('failed', "Failed to delete data material!");
        }

        return redirect(route('materials.index'));
    }
}
