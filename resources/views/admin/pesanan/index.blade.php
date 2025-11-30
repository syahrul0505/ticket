@extends('admin.layouts.app')

@push('style-link')
<link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/light/components/tabs.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/components/tabs.css') }}" rel="stylesheet" type="text/css">
{{-- Date Picker --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .hilang{
      display: none !important;
    }
    .dark-grey{
        color: #515365 !important;
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
@include('admin.components.alert')
<div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
    <div class="card">
        <div class="card-body">
            <form action="" method="get" class="row g-3 align-items-center">
            {{-- <div class="row g-3 align-item-cente"> --}}
               <div class="col-12 col-md-3">
                   <label class="form-label"> Period :</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-calendar-minus"></i></span>
                        <select class="form-control select2" data-placeholder="Choose one" id="daterange" name="type">
                            <option value="day" {{ (Request::get('type') == 'day') ? 'selected' : ''}}>Daily </option>
                            <option value="monthly" {{ (Request::get('type') == 'monthly') ? 'selected' : '' }}>Monthly </option>
                            <option value="yearly" {{ (Request::get('type') == 'yearly') ? 'selected' : '' }}>Yearly </option>
                        </select>
                    </div>
               </div>
               <div class="col-12 col-md-4">
                    <div class="" id="datepicker-date-area">
                        <label class="form-label"> Date :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="start_date" id="date" value="{{Request::get('start_date') ?? date('Y-m-d')}}" autocomplete="off" class="datepicker-date form-control time" required>
                        </div>
                    </div>
                    <div class="hilang" id="datepicker-month-area">
                        <label class="form-label"> Month :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="month" id="month" value="{{ Request::get('month') ?? date('Y-m') }}" autocomplete="off" class="datepicker-month form-control time" required>
                        </div>
                    </div>
                    <div class="hilang" id="datepicker-year-area">
                        <label class="form-label"> Year :</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="text" name="year" id="year" value="{{ Request::get('year') ?? date('Y') }}" autocomplete="off" class="datepicker-year form-control" required>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group mt-4">
                        <button  id="generate" class="btn btn-primary btn-sm p-2 w-100">
                            Generate
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group mt-4">
                        <a href="{{ route('pos') }}" class="btn btn-danger px-4">Back</a>
                    </div>
                </div>
            {{-- </div> --}}
            </form><!--end row-->
        </div>
    </div>
</div>
<div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
    @foreach ($orders as $item)
        <div class="accordion" id="accordionExample-{{ $item->id }}">
            <div class="accordion-item" style="border-color: #3d3d3d !important;">
                <h2 class="accordion-header" id="headingOne-{{ $item->id }}">
                    @if ($item->payment_status == "Paid" && $item->status_product == "Sewa")
                        <button class="accordion-button collapsed bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $item->id }}" aria-expanded="false" aria-controls="collapseOne-{{ $item->id }}">
                    @elseif ($item->payment_status == "Paid")
                        <button class="accordion-button collapsed bg-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $item->id }}" aria-expanded="false" aria-controls="collapseOne-{{ $item->id }}">
                    @else
                        <button class="accordion-button collapsed bg-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{ $item->id }}" aria-expanded="false" aria-controls="collapseOne-{{ $item->id }}">
                    @endif

                        <div class="ms-3 d-block">
                            <h6 class="mb-0">#{{ $item->no_invoice }} ({{ $item->cashier_name }})</h6>
                            <?php
                            $invoiceNumber = $item->no_invoice;
                            $parts = explode('-', $invoiceNumber); // Memisahkan nomor invoice menjadi bagian terpisah
                            $lastPart = end($parts); // Mengambil bagian terakhir dari nomor invoice

                            // Menambahkan 'CUST' setelah tanda '-' terakhir
                            $newInvoiceNumber = $parts[0] . '-' .'CUST'.$lastPart ;

                            ?>
                            <h3 class="mb-0">{{ $item->customer_name ?? $newInvoiceNumber }}</h3>
                            <div class="mt-1">
                                <span class="badge badge-light-primary mb-2 me-4">{{ $item->payment_method }}</span>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="collapseOne-{{ $item->id }}" class="accordion-collapse collapse" aria-labelledby="headingOne-{{ $item->id }}" data-bs-parent="#accordionExample-{{ $item->id }}">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <ul class="list-group list-group-flush">
                                    @foreach ($item->orderProducts as $orderProduct)
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 style="color: #515365">{{ $orderProduct->name }}</h4>
                                                <small style="color: #515365">x{{ $orderProduct->qty }} </small>
                                            </div>
                                            <p class="mb-1">Rp. {{ number_format($orderProduct->selling_price * $orderProduct->qty,0)  }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    <div class="card-header bg-card-head py-2 px-3 text-center">
                                        <span class="tx-bold text-lg text-white" style="font-size:20px;">
                                            Summary Order
                                        </span>
                                    </div>

                                    @php
                                        $totalPrice = 0;
                                    @endphp

                                    @foreach ($item->orderProducts as $orderProduct)
                                        @php
                                        // Calculate the running total for each item
                                        $totalPrice += $orderProduct->price_discount * $orderProduct->qty ;
                                        @endphp
                                    @endforeach

                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Sub total</strong></h4>
                                                <span>Rp.{{ number_format($item->subtotal,0) }}</span>
                                            </div>
                                        </li>

                                        @if ($item->is_coupon == true)
                                        {{-- @if ($item->is_coupon == false ) --}}
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                @foreach ($item->orderCoupons as $orderCoupon)
                                                <div class="d-flex flex-row align-items-center">
                                                    <h4 class="mb-1 dark-grey"><strong>Coupon</strong></h4>
                                                    <span class="fs-6"> ({{ $orderCoupon->name ?? '-' }})</span>
                                                </div>
                                                <span>Rp.{{ number_format($orderCoupon->discount_value,0) }}</span>
                                                @endforeach
                                            </div>
                                        </li>
                                        @elseif($item->type_discount)
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="d-flex flex-row align-items-center">
                                                    <h4 class="mb-1 dark-grey"><strong>Discount</strong></h4>
                                                    <span class="fs-6"> ({{ $item->type_discount ?? '-' }})</span>
                                                </div>
                                                <span>
                                                    @if($item->type_discount == 'percent')
                                                        {{ $item->percent_discount }}%
                                                    @else
                                                        Rp.{{ number_format($item->price_discount,0) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </li>
                                        @endif
                                        
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>PB01 :</strong></h4>
                                                <span>Rp.{{ number_format($item->pb01,0) }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Service :</strong></h4>
                                                <span>Rp.{{ number_format($item->service,0) }}</span>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Sewa :</strong></h4>
                                                <span>{{ number_format($item->sewa,0) }} Malam</span>
                                            </div>
                                        </li>

                                        @if ($item->start_date)
                                            
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Tanggal Sewa :</strong></h4>
                                                <span>{{ ($item->start_date) }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Akhir Sewa :</strong></h4>
                                                <span>{{ ($item->end_date) }}</span>
                                            </div>
                                        </li>
                                        @endif

                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Total Payment :</strong></h4>
                                                <span>Rp.{{ number_format($item->total,0) }}</span>
                                            </div>
                                        </li>
                                        
                                        @if ($item->kembalian != 0)
                                            <li class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h4 class="mb-1 dark-grey"><strong>Cash :</strong></h4>
                                                    <span>Rp.{{ number_format($item->cash,0) }}</span>
                                                </div>
                                            </li>

                                            <li class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h4 class="mb-1 dark-grey"><strong>Kembalian :</strong></h4>
                                                    <span>Rp.{{ number_format($item->kembalian,0) }}</span>
                                                </div>
                                            </li>
                                        @endif

                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Metode Pembayaran</strong></h4>
                                                <span>{{ $item->payment_method }}</span>
                                            </div>
                                        </li>

                                        <li class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h4 class="mb-1 dark-grey"><strong>Metode Pembayaran 2</strong></h4>
                                                <span>{{ $item->payment_method2 ?? '-' }}</span>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                @if ($item->payment_status_midtrans != 'Paid' && $item->status_product == 'Sewa')
                                                <div class="col-lg-6">
                                                    <button type="button" class="btn btn-sm w-100 btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal-{{ $item->id }}">Update Payment</button>
                                                </div>
                                                @endif

                                                @if ($item->payment_status == 'Paid')
                                                <div class="col-lg-6">
                                                    <form action="{{ route('cancel-order', $item->id) }}" method="POST">
                                                        @method('patch')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm w-100 btn-danger">Cancel Order</button>

                                                    </form>
                                                </div>
                                                @endif

                                                <!-- Modal -->
                                                <div class="modal fade" id="exampleModal-{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <form action="{{ route('return-order', $item->id) }}" method="POST">
                                                            @method('patch')
                                                            @csrf
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="exampleModalLabel">Apakah Anda Yakin Ingin Menyelesaikan Pembayaran</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group">
                                                                        <h6 class="mb-3">Apakah Ada denda</h6>
                                                                        <select name="status_denda" id="status_denda" class="form-control form-control-sm payment-method" data-modal-id="{{ $item->id }}">
                                                                            <option value="Ada">Ada</option>
                                                                            <option value="Tidak">Tidak</option>
                                                                        </select>
                                                                    </div>

                                                                    <div id="denda_section">
                                                                        <div class="form-group mb-2">
                                                                            <label for="denda" class="form-label">Denda Malam</label>
                                                                            <input type="text" name="denda" value="{{ old('denda') }}" class="form-control form-control-sm" placeholder="Ex:1 Hari" id="denda">
                                                                        </div>
                                                                        <div class="form-group mb-2">
                                                                            <label for="denda_barang_rusak" class="form-label">Denda Barang Rusak</label>
                                                                            <input type="text" name="denda_barang_rusak" value="{{ old('denda_barang_rusak') }}" class="form-control form-control-sm" placeholder="Ex:10.000" id="denda_barang_rusak">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <h6 class="mb-3">Metode Payment</h6>
                                                                            <select name="payment_method" class="form-control form-control-sm payment-method" data-modal-id="{{ $item->id }}">
                                                                                <option selected value="Transfer Bank">Transfer Bank</option>
                                                                                <option value="Qris">Qris</option>
                                                                                <option value="Cash">Cash</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group mt-2 cash-input" id="cashInput-{{ $item->id }}" style="display: none;">
                                                                        <label for="cash" class="form-label">Cash</label>
                                                                        <input type="text" name="cash" value="{{ old('cash') }}" class="form-control form-control-sm" placeholder="Ex:50.000" id="cash" aria-describedby="cash">
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    {{-- <button class="btn btn-light-dark" data-bs-dismiss="modal">Discard</button> --}}
                                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                
                                                <div class="row">
                                                    @if ($item->status_product == 'Sewa')
                                                    <div class="col-lg-6 mt-2">
                                                        <a href="{{ route('print-struk', $item->id) }}" target="_blank" type="submit" class="btn btn-sm w-100 btn-warning">Print Struk</a>
                                                    </div>
                                                    
                                                    <div class="col-lg-6 mt-2">
                                                        <a href="{{ route('struk-done', $item->id) }}" target="_blank" type="submit" class="btn btn-sm w-100 btn-success">Struk Selesai</a>
                                                    </div>
                                                    @else
                                                    <div class="col-lg-6 mt-2">
                                                        <a href="{{ route('print-product', $item->id) }}" target="_blank" type="submit" class="btn btn-sm w-100 btn-warning">Print Struk</a>
                                                    </div>
                                                    @endif
                                                </div>

                                                @if ($item->customer_phone)
                                                <div class="col-lg-6 mt-2">
                                                    <form action="{{ route('send-wa',$item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm w-100 btn-secondary" type="button">
                                                            <small class="text-white">Send Wa</small>
                                                        </button>
                                                    </form>
                                                </div>

                                                @if ($item->status_product == 'Sewa' || $item->status_product == 'Sewa Paid')
                                                <div class="col-lg-6 mt-2">
                                                    <form action="{{ route('send-wa-done',$item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm w-100 btn-secondary" type="button">
                                                            <small class="text-white">Send Wa Done</small>
                                                        </button>
                                                    </form>
                                                </div>
                                                @endif
                                                @endif
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let statusDenda = document.getElementById("status_denda");
        let dendaSection = document.getElementById("denda_section");

        function toggleDenda() {
            if (statusDenda.value === "Ada") {
                dendaSection.style.display = "block";
            } else {
                dendaSection.style.display = "none";
            }
        }

        statusDenda.addEventListener("change", toggleDenda);
        toggleDenda(); // Jalankan saat halaman dimuat
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.payment-method').forEach(selectInput => {
        const modalId = selectInput.getAttribute('data-modal-id');
        const cashInput = document.getElementById(`cashInput-${modalId}`);

        function handleCashInputDisplay() {
            if (selectInput.value === 'Cash') {
                cashInput.style.display = 'block';
            } else {
                cashInput.style.display = 'none';
            }
        }

        // Set initial state
        handleCashInputDisplay();

        // Add change event listener
        selectInput.addEventListener('change', handleCashInputDisplay);
    });
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
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
