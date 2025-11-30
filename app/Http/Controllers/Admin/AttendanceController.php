<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Attendance\AddAttendanceRequest;
use App\Models\AdditionalIncome;
use App\Models\Attendance;
use App\Models\OtherSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    // function __construct()
    // {
    //     $this->middleware('permission:attendance-list', ['only' => ['index', 'getAttendances']]);
    //     $this->middleware('permission:attendance-create', ['only' => ['getModalAdd','store']]);
    //     $this->middleware('permission:attendance-edit', ['only' => ['getModalEdit','update']]);
    //     $this->middleware('permission:attendance-delete', ['only' => ['getModalDelete','destroy']]);
    // }

    public function index(Request $request)
    {
        $data['page_title'] = 'Absensi List';

        $user = Auth::user( ); // Get the currently authenticated user

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

        // Initialize $attendance as an empty collection
        $attendance = collect();

        if ($type == 'day') {
            if ($user == 'All' || $user == null) {
                $now = Carbon::parse($request->input('start_date', Carbon::today()->toDateString()))->toDateString();

                $attendance = Attendance::whereDate('created_at', $now)
                                        ->orderBy('id', 'desc')
                                        ->get();
                                        
                $additionalIncome = AdditionalIncome::whereDate('created_at', $now)
                                        ->orderBy('id', 'desc')
                                        ->get();

            } else {
                $attendance = Attendance::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();

                $additionalIncome = AdditionalIncome::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $attendance = Attendance::whereMonth('created_at', $monthPart)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();

            $additionalIncome = AdditionalIncome::whereMonth('created_at', $monthPart)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $attendance = Attendance::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();

            $additionalIncome = AdditionalIncome::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        }

        // Calculate the total salary based on the attendance data
        $data['totalSalary'] = $attendance->sum('total_salary') + $additionalIncome->sum('amount');

        // Pass the data to the view
        return view('admin.attendance.index', $data);
    }




    public function getAttendances(Request $request)
    {
        $type = $request->input('type', 'day');
        $user = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        // Initialize $orders as an empty collection
        $attendance = collect();

        if ($type == 'day') {
            if ($user == 'All') {
                $attendance = Attendance::whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            } else {
                $attendance = Attendance::where('user_id', $user)
                            ->whereDate('created_at', $date)
                            ->orderBy('id', 'desc')
                            ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $attendance = Attendance::whereMonth('created_at', $monthPart)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $attendance = Attendance::whereYear('created_at', $year)
                        ->when($user != 'All', function ($query) use ($user) {
                            return $query->where('user_id', $user);
                        })
                        ->orderBy('id', 'desc')
                        ->get();
        }

        if ($request->ajax()) {
            return DataTables::of($attendance)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->fullname : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<button type="button" class="btn btn-sm btn-warning attendance-edit-table" data-bs-target="#tabs-' . $row->id . '-edit-attendance">Edit</button>';
                    $btn .= ' <button type="button" class="btn btn-sm btn-danger attendance-delete-table" data-bs-target="#tabs-' . $row->id . '-delete-attendance">Delete</button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }



    public function store(Request $request)
    {
        try {
            $attendance = new Attendance();
            $attendance->user_id    = Auth::id();
            $attendance->date       = Carbon::today()->toDateString();
            $attendance->check_in   = $request->check_in;
            $attendance->status     = $this->determineStatus($request->check_in);

            $attendance->save();

            return response()->json([
                'code' => 200,
                'message' => 'Check In successful',
                'data' => $attendance
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to create attendance data',
                'data' => []
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $attendance = Attendance::find($id);

            if (!$attendance) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Attendance not found',
                    'data' => []
                ], 404);
            }

            // Ambil pengaturan gaji dari other_settings
            $otherSetting = OtherSetting::first();
            $regularSalaryPerMinute = $otherSetting->regular_day_salary;
            $holidaySalaryPerMinute = $otherSetting->holiday_salary;

            // Pastikan date hanya berisi YYYY-MM-DD
            $attendanceDate = Carbon::parse($attendance->date);

            // Ambil hanya jam dari check_out request
            $checkOutTime = Carbon::parse($request->check_out)->format('H:i:s');

            // Gabungkan date dari attendance dan time dari check_out
            $checkOut = Carbon::parse("{$attendance->date} {$checkOutTime}");

            // Hitung total menit kerja
            $checkIn = Carbon::parse("{$attendance->date} {$attendance->check_in}");
            $totalMinutes = $checkIn->diffInMinutes($checkOut);

            // Cek apakah hari ini hari Minggu atau libur nasional
            $isSunday = $attendanceDate->isSunday();
            $isHoliday = $this->isNationalHoliday($attendanceDate->format('Y-m-d'));

            // Gunakan gaji hari libur jika hari Minggu atau libur nasional
            $salaryPerMinute = ($isSunday || $isHoliday) ? $holidaySalaryPerMinute : $regularSalaryPerMinute;

            // Hitung total gaji
            $totalSalary = $totalMinutes * $salaryPerMinute;

            // Simpan data
            $attendance->check_out = $checkOutTime;
            $attendance->total_minute = $totalMinutes;
            $attendance->total_salary = $totalSalary;
            $attendance->save();

            return response()->json([
                'code' => 200,
                'message' => 'Check Out successful',
                'data' => $attendance
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'message' => 'Failed to update attendance data',
                'data' => [],
                'error' => $th->getMessage()
            ], 500);
        }
    }



    public function checkAbsensi()
    {
        try {
            // Menggunakan Carbon untuk mendapatkan tanggal hari ini
            $today = Carbon::today();

            // Mencari data kehadiran berdasarkan tanggal hari ini dan ID pengguna yang sedang login
            $attendance = Attendance::whereDate('date', $today)->where('user_id', Auth::user()->id)->first();

            if (!$attendance) {
                $response = [
                    'code'    => 404,
                    'message' => 'User not found!',
                    'data'    => []
                ];
                return response()->json($response, 200);
            }

            $response = [
                'code'    => 200,
                'message' => 'Get data attendance successfully!',
                'data'    => $attendance
            ];
            return response()->json($response, 200);
        } catch (\Throwable $th) {
            $response = [
                'code'    => 500,
                'message' => 'Internal server error',
                'data'    => []
            ];
            return response()->json($response, 500);
        }
    }

    private function determineStatus($checkInTime)
    {
        $standardCheckInTime = Carbon::createFromTime(9, 0, 0);
        $checkIn = Carbon::parse($checkInTime);

        return $checkIn->lessThanOrEqualTo($standardCheckInTime) ? 'on_time' : 'late';
    }

    private function isNationalHoliday($date)
    {
        try {
            $response = Http::get('https://dayoffapi.vercel.app/api');
            $holidays = $response->json();

            if ($holidays) {
                foreach ($holidays as $holiday) {
                    // Hanya ambil libur nasional yang is_cuti = false
                    if ($holiday['tanggal'] === $date && !$holiday['is_cuti']) {
                        return true; // Jika tanggal ditemukan dalam daftar libur nasional
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch holiday API: ' . $e->getMessage());
        }
        
        return false; // Default tidak libur
    }

}
