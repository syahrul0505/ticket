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

  .dt-buttons {
    margin-top: 10px;
    margin-bottom: 10px;
}

.dt-buttons .btn {
    margin-right: 5px;
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
<div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
    @include('admin.components.alert')
    <div class="widget-content widget-content-area br-8">
        <table id="customers-table" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>No Invoice</th>
                    <th>Waktu Order</th>
                    <th>Nama Kasir</th>
                    <th>Nama Customer</th>
                    <th>Nomor Pelanggan</th>
                    <th>Menu</th>
                    <th>Metode Pembayaran</th>
                    <th>Payment Status</th>
                    <th>Sewa</th>
                    <th>Tanggal Sewa</th>
                    <th>Tanggal Penggembalian</th>
                    <th>Sub Total</th>
                    <th>Type Discount</th>
                    <th>Discount Price</th>
                    <th>Discount Percent</th>
                    <th>Service</th>
                    <th>PB01</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <input type="hidden" name="phone" id="phone" value="{{Request::get('phone') ?? date('Y-m-d')}}" autocomplete="off" class="">
        </table>
    </div>
</div>

<div id="modalContainer"></div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>


<script>
$(document).ready(function() {
    $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.history-customer') }}",
            data: function(d) {
                d.phone = $('#phone').val();
                console.log(d.phone);
            },
            error: function(xhr, textStatus, errorThrown) {
                $('#report-gross-table').DataTable().clear().draw();
                console.log(xhr.responseText);
                alert('There was an error fetching data. Please try again later.');
            }
        },
        columns: [
            {
                "data": 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {data: 'no_invoice', name: 'no_invoice'},
            {
                data: 'created_at',
                render: function(data) {
                    return moment(data).format('YYYY-MM-DD HH:mm:ss');
                },
                name: 'created_at'
            },
            {data: 'cashier_name', name: 'cashier_name'},
            {data: 'customer_name', name: 'customer_name'},
            {data: 'customer_phone', name: 'customer_phone'},
            {data: 'order_products', name: 'order_products', orderable: false, searchable: false},
            {data: 'payment_method', name: 'payment_method'},
            {
                data: 'payment_status',
                render: function(data) {
                    if (data == 'Paid') {
                        return `<span class="badge badge-success">${data}</span>`;
                    } else {
                        return `<span class="badge badge-danger">${data}</span>`;
                    }
                }
            },
            {
                data: 'sewa',
                render: function(data) {
                    if (data == '0') {
                        return `${data} malam`;
                    } else {
                        return `${data} malam`;
                    }
                }
            },
            {data: 'start_date', name: 'start_date'},
            {data: 'end_date', name: 'end_date'},
            {
                data: 'subtotal',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {data: 'type_discount', name: 'type_discount'},
            {
                data: 'price_discount',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: 'percent_discount',
                render: function(data) {
                    if (data) {
                        return data + '%';
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: 'service',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: 'pb01',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {
                data: 'total',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {data: 'action', name: 'action'},
        ],
        dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f<'toolbar align-self-center'>>>>" +
             "<'table-responsive'tr>" +
             "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>" +
             "<'dt-buttons'B>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                className: 'btn btn-success'
            },
            // {
            //     extend: 'pdfHtml5',
            //     text: 'Export to PDF',
            //     className: 'btn btn-danger'
            // }
        ],
        oLanguage: {
            oPaginate: {
                sPrevious: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                sNext: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
            },
            sInfo: "Showing page _PAGE_ of _PAGES_",
            sSearch: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
            sSearchPlaceholder: "Search...",
            sLengthMenu: "Results :  _MENU_",
        },
        stripeClasses: [],
        lengthMenu: [10, 20, 50],
        pageLength: 10
    });

    // Event Reset by Modal
    $(document).on('click', '.customers-reset-table', function() {
            var customerId = $(this).data('bs-target');
            var parts = customerId.split("-");
            var id = parseInt(parts[1]);

            $.get("{{ url('customers/modal-reset') }}/" + id, function(data) {
                $('#modalContainer').html(data);
                $(`${customerId}`).modal('show');
            });
        });

});
</script>
@endpush
