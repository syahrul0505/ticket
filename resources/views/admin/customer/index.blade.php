@extends('admin.layouts.app')

@push('style-link')
<link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/light/components/tabs.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/components/tabs.css') }}" rel="stylesheet" type="text/css">
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
    @include('admin.components.alert')
    <div class="widget-content widget-content-area br-8">
        <table id="customers-table" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Instagram</th>
                    <th>No Telepon</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th class="no-content" width="10%">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="modalContainer"></div>
@endsection

@push('js')
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
        // getData
        $('#customers-table').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: "{{ route('customers.get-data') }}",
                error: function(xhr, textStatus, errorThrown) {
                    $('#customers-table').DataTable().clear().draw(); // Bersihkan tabel
                    console.log(xhr.responseText); // Debug di console
                    alert('There was an error fetching data. Please try again later.');
                }
            },
            columns: [
                {
                    "data": 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'code',
                    name: 'code',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'instagram',
                    name: 'instagram',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'address',
                    name: 'address',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        if (data === 'Sewa') {
                            return `<span class="badge badge-warning">${data}</span>`;
                        } else if (data === 'Selesai') {
                            return `<span class="badge badge-success">${data}</span>`;
                        } else {
                            return `<span class="badge badge-secondary">Selesai</span>`;
                        }
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ],
            dom: "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'Bf<'toolbar align-self-center'>>>>" +
                "<'table-responsive'tr>" +
                "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Export Semua Data',
                    className: 'btn btn-success',
                    exportOptions: {
                        modifier: {
                            page: 'all'
                        }
                    }
                },
                // {
                //     extend: 'csvHtml5',
                //     text: 'Export CSV',
                //     className: 'btn btn-info'
                // },
                // {
                //     extend: 'print',
                //     text: 'Print',
                //     className: 'btn btn-secondary'
                // }
                // {
                //     extend: 'pdfHtml5',
                //     text: 'Export PDF',
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


        $("div.toolbar").html('<button class="ms-2 btn btn-primary customers-add" type="button" data-bs-target="#tabs-add-customer">'+
                                '<span>Create Customer</span>'+
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>'+
                            '</button>');

        // Event create by Modal
        $(document).on('click', '.customers-add', function() {
            var getTarget = $(this).data('bs-target');

            $.get("{{ route('customers.modal-add') }}", function(data) {
                $('#modalContainer').html(data);
                $(`${getTarget}`).modal('show');
            });
        });

        // Event Edit by Modal
        $(document).on('click', '.customers-edit-table', function() {
            var customerId = $(this).data('bs-target');
            var parts = customerId.split("-");
            var id = parseInt(parts[1]);

            $.get("{{ url('customers/modal-edit') }}/" + id, function(data) {
                $('#modalContainer').html(data);
                $(`${customerId}`).modal('show');
            });
        });

        // Event Delete by Modal
        $(document).on('click', '.customers-delete-table', function() {
            var customerId = $(this).data('bs-target');
            var parts = customerId.split("-");
            var id = parseInt(parts[1]);

            $.get("{{ url('customers/modal-delete') }}/" + id, function(data) {
                $('#modalContainer').html(data);
                $(`${customerId}`).modal('show');
            });
        });

    });
</script>
@endpush
