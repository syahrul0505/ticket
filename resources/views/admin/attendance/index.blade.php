@extends('admin.layouts.app')

@push('style-link')
<link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/light/components/tabs.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/components/tabs.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('src/assets/css/light/dashboard/dash_1.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/dashboard/dash_1.css') }}" rel="stylesheet" type="text/css" />

{{-- Date Picker --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .btn-custom {
        border-radius: 15px !important;
    }

    .hilang{
        display: none !important;
    }
</style>
@endpush

@section('breadcumbs')
<nav class="breadcrumb-style-one" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">{{ $page_title }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
    <div class="card">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="get" class="row g-3 align-items-center">
                {{-- Filter form --}}
                <div class="col-12 col-md-3">
                    <label class="form-label"> Periode :</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-calendar-minus"></i></span>
                        <select class="form-control select2" data-placeholder="Choose one" id="daterange" name="type">
                            <option value="day" {{ (Request::get('type') == 'day') ? 'selected' : ''}}>Harian </option>
                            <option value="monthly" {{ (Request::get('type') == 'monthly') ? 'selected' : '' }}>Bulanan </option>
                            <option value="yearly" {{ (Request::get('type') == 'yearly') ? 'selected' : '' }}>Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="" id="datepicker-date-area">
                        <label class="form-label"> Tanggal :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="start_date" id="date" value="{{Request::get('start_date') ?? date('Y-m-d')}}" autocomplete="off" class="datepicker-date form-control time" required>
                        </div>
                    </div>
                    <div class="hilang" id="datepicker-month-area">
                        <label class="form-label"> Bulan :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="month" id="month" value="{{ Request::get('month') ?? date('Y-m') }}" autocomplete="off" class="datepicker-month form-control time" required>
                        </div>
                    </div>
                    <div class="hilang" id="datepicker-year-area">
                        <label class="form-label"> Tahun :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="year" id="year" value="{{ Request::get('year') ?? date('Y') }}" autocomplete="off" class="datepicker-year form-control" required>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">User :</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                        <select class="form-control @error('user_id') is-invalid @enderror" id="user" name="user_id">
                            @if (in_array('super-admin', Auth::user()->getRoleNames()->toArray()))
                                <option value="All" {{ Request::get('user_id') == 'All' ? 'selected' : '' }}>All</option>
                            @endif
                            @foreach ($account_users as $account_user)
                                <option value="{{ $account_user->id }}" 
                                    {{ (Request::get('user_id') == $account_user->id || (Request::get('user_id') == null && Auth::user()->id == $account_user->id)) ? 'selected' : '' }}>
                                    {{ $account_user->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-12 col-md-2">
                    <div class="form-group mt-4">
                        <button  id="generate" class="btn btn-primary btn-sm p-2 w-100">
                            Generate
                        </button>
                    </div>
                </div>
            </form><!--end row-->
        </div>
    </div>
</div>

@can('attendance-list')
    {{-- Only show Total Gaji if specific query parameters are present --}}
        <div class="row mt-3" style="z-index: -99999999">
            <div class="col-12 col-md-4 col-lg-6">
                <div class="card radius-10 bg-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Total Gaji</p>
                                <h4 class="my-1 text-white">Rp. {{ number_format($totalSalary, 1, ',', '.') }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-danger ms-auto">
                                <i class="bx bx-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="col-12 col-md-4 col-lg-6">
                <div class="card radius-10 bg-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-white">Bonus</p>
                                <h4 class="my-1 text-white">Rp. {{ number_format($totalBonus, 0) }}</h4>
                            </div>
                            <div class="widgets-icons bg-white text-danger ms-auto">
                                <i class="bx bx-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
@endcan


<div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing mt-4">
    @include('admin.components.alert')
    <div class="widget widget-card-one">
        <div class="widget-content">

            <div class="media">
                <div class="w-img">
                    <img src="../src/assets/img/profile-19.jpeg" alt="avatar">
                </div>
                <div class="media-body d-flex justify-content-between align-items-center ms-2">
                    <div class="">
                        <h5 class="fw-bold mb-0 pb-0">{{ Auth::user()->fullname }}</h5>
                        <p class="meta-date-time">{{ str_replace(['-', '_'], ' ',Auth::user()->getRoleNames()[0]) }}</p>

                        <ul class="list-group mt-3">
                            <li class="list-group-item bg-transparent border-0 p-0">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="flex-1">
                                        <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mg-b-0 text-white opacity-75">{{ Auth::user()->phone ?? 'Nomer telephone belum di cantumkan' }}</p>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item bg-transparent border-0 p-0">
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="flex-1">
                                        <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mg-b-0 text-white opacity-75">{{ Auth::user()->email ?? 'Email belum di cantumkan' }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="">
                        <button class="btn btn-success btn-custom" id="attendanceButton">Check In</button>
                    </div>
                </div>
            </div>

            @can('attendance-list')
            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing bg-transparent">
                @include('admin.components.alert')
                <div class="widget-content widget-content-area br-8 border-0" style="background: transparent !important; box-shadow:none;">
                    <table id="attendances-table" class="table dt-table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="7%">No</th>
                                <th>Nama Pengguna</th>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Gaji</th>
                                <th>Total Menit</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>

<div id="modalContainer"></div>

@endsection

@push('js-src')
<script src="{{ asset('src/plugins/src/tomSelect/tom-select.base.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        let attendanceData = null;

        $.ajax({
            url: `{{ route('attendances.check') }}`,
            type: 'GET',
            success: function(data) {
                if (data.code == 404) {
                    $('#attendanceButton')
                        .removeClass('btn-danger')
                        .addClass('btn-success')
                        .text('Check In')
                        .off('click')
                        .on('click', function() {
                            postCheckIn();
                        });
                } else if (data.code == 200 && data.data.check_out == null && data.data.check_in) {
                    attendanceData = data.data;
                    $('#attendanceButton')
                        .removeClass('btn-success')
                        .addClass('btn-danger')
                        .text('Check Out')
                        .off('click')
                        .on('click', function() {
                            postCheckOut(attendanceData.id);
                        });
                } else {
                    $('#attendanceButton')
                        .removeClass('btn-danger')
                        .addClass('btn-success')
                        .addClass('disabled')
                        .text('Check In');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to absensi: ', error);
            }
        });

        function postCheckIn() {
            $.ajax({
                url: `{{ route('attendances.store') }}`,
                type: 'POST',
                data: {
                    _token: `{{ csrf_token() }}`,
                    check_in: getIndonesiaTime(),
                },
                success: function(data) {
                    alert('Check In successful');
                    location.reload(); // Reload the page to refresh the button state
                },
                error: function(xhr, status, error) {
                    console.error('Failed to Check In: ', error);
                    alert('Failed to Check In');
                }
            });
        }

        function postCheckOut(attendanceId) {
            $.ajax({
                url: `{{ route('attendances.update', '') }}/${attendanceId}`,
                type: 'PUT',
                data: {
                    _token: `{{ csrf_token() }}`,
                    check_out: getIndonesiaTime(),
                },
                success: function(data) {
                    alert('Check Out successful');
                    location.reload(); // Reload the page to refresh the button state
                },
                error: function(xhr, status, error) {
                    console.error('Failed to Check Out: ', error);
                    alert('Failed to Check Out');
                }
            });
        }

        function getIndonesiaTime() {
            const now = new Date();

            // Convert the current time to the Indonesia time zone (UTC+7)
            const options = {
                timeZone: 'Asia/Jakarta',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };

            // Format the time to YYYY-MM-DD HH:MM:SS
            const formatter = new Intl.DateTimeFormat('en-GB', options);
            const parts = formatter.formatToParts(now);
            const formattedDateTime = `${parts[4].value}-${parts[2].value}-${parts[0].value} ${parts[6].value}:${parts[8].value}:${parts[10].value}`;

            return formattedDateTime;
        }

        // getData
        $('#attendances-table').DataTable({
            processing: true,
            serverSide:true,
            ajax: {
            url: "{{ route('attendances.get-data') }}",
            data: function(d) {
                d.type = $('#daterange').val(); 
                d.user_id = $('#user').val(); 
                d.start_date = $('#date').val(); 
                d.month = $('#month').val(); 
                d.year = $('#year').val(); 
            },
            error: function(xhr, textStatus, errorThrown) {
                $('#attendances-table').DataTable().clear().draw(); // Bersihkan tabel
                console.log(xhr.responseText); // Tampilkan pesan kesalahan di konsol browser
                alert('There was an error fetching data. Please try again later.'); // Tampilkan pesan kesalahan kepada pengguna
            }
            },
            columns: [
                {
                        "data": 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                },
                {data: 'user_name', name:'user_name'},
                {data: 'date', name:'date'},
                {data: 'check_in', name:'check_in'},
                {data: 'check_out', name:'check_out'},
                {
                    data: 'total_salary',
                    render: function(data) {
                        if (data != 0) {
                            return formatRupiah(data); // Format gaji
                        } else {
                            return `<span class="badge badge-danger">-</span>`; // Jika gaji 0, tampilkan tanda -
                        }
                    }
                },
                {data: 'total_minute', name:'total_minute'},
            ],
            "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f<'toolbar align-self-center'>>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
            "oLanguage": {
                "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                "sInfo": "Showing page _PAGE_ of _PAGES_",
                "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                "sSearchPlaceholder": "Search...",
            "sLengthMenu": "Results :  _MENU_",
            },
            "stripeClasses": [],
            "lengthMenu": [10, 20, 50],
            "pageLength": 10
        });

        function formatRupiah(amount) {
            const numberString = amount.toString();
            const split = numberString.split('.'); // Memisahkan bagian integer dan desimal jika ada
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            const ribuan = split[0].substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return 'Rp. ' + rupiah + (split[1] ? ',' + split[1] : '');
        }

        // Event create by Modal
        $(document).on('click', '.attendances-add', function() {
            var getTarget = $(this).data('bs-target');

            $.get("{{ route('attendances.modal-add') }}", function(data) {
                $('#modalContainer').html(data);
                $(`${getTarget}`).modal('show');
            });
        });
    });
</script>

<script>
    $('.datepicker-date').datepicker({
      format: "yyyy-mm-dd",
        startView: 2,
        minViewMode: 0,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: 'body'
    });

    $('.datepicker-month').datepicker({
        format: "yyyy-mm",
        startView: 2,
        minViewMode: 1,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: 'body'
    });

    $('.datepicker-year').datepicker({
        format: "yyyy",
        startView: 2,
        minViewMode: 2,
        language: "id",
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true,
        toggleActive: true,
        container: 'body'
    });

    let rangeNow = $('#daterange').val();
    if (rangeNow == 'day') {
        $('#datepicker-date-area').removeClass('hilang');
        const element = document.querySelector('#datepicker-date-area')
        element.classList.add('animated', 'fadeIn')
        // Hilangkan Month
        $('#datepicker-month-area').addClass('hilang');
        $('#datepicker-year-area').addClass('hilang');

    } else if(rangeNow == 'monthly') {
        $('#datepicker-month-area').removeClass('hilang');
        const element = document.querySelector('#datepicker-month-area')
        element.classList.add('animated', 'fadeIn')
        // Hilangkan Date
        $('#datepicker-date-area').addClass('hilang');
        $('#datepicker-year-area').addClass('hilang');
    } else {
        $('#datepicker-year-area').removeClass('hilang');
        const element = document.querySelector('#datepicker-year-area')
        element.classList.add('animated', 'fadeIn')
        // Hilangkan Date
        $('#datepicker-date-area').addClass('hilang');
        $('#datepicker-month-area').addClass('hilang');
    }

    $('#daterange').on('change', function () {
        val = $(this).val();
        if (val == 'day') {
            $('#datepicker-date-area').removeClass('hilang');
            const element = document.querySelector('#datepicker-date-area')
            element.classList.add('animated', 'fadeIn')
            // Hilangkan Month
            $('#datepicker-month-area').addClass('hilang');
            $('#datepicker-year-area').addClass('hilang');

        } else if(val == 'monthly') {
            $('#datepicker-month-area').removeClass('hilang');
            const element = document.querySelector('#datepicker-month-area')
            element.classList.add('animated', 'fadeIn')
            // Hilangkan Date
            $('#datepicker-date-area').addClass('hilang');
            $('#datepicker-year-area').addClass('hilang');
        } else {
            $('#datepicker-year-area').removeClass('hilang');
            const element = document.querySelector('#datepicker-year-area')
            element.classList.add('animated', 'fadeIn')
            // Hilangkan Date
            $('#datepicker-date-area').addClass('hilang');
            $('#datepicker-month-area').addClass('hilang');
        }
    })
</script>
@endpush
