@extends('admin.layouts.app')

@push('style-link')
<link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/light/components/tabs.css') }}" rel="stylesheet" type="text/css">

<link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('src/assets/css/dark/components/tabs.css') }}" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/src/tomSelect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/light/tomSelect/custom-tomSelect.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/dark/tomSelect/custom-tomSelect.css') }}">
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
        <table id="income-category-table" class="table dt-table-hover" style="width:100%">
            <thead>
                <tr>
                    <th width="7%">No</th>
                    <th>Nama</th>
                    <th>Jumlah Harga</th>
                    <th class="no-content" width="10%">Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="modalContainer"></div>
@endsection

@push('js-src')
<script src="{{ asset('src/plugins/src/tomSelect/tom-select.base.js') }}"></script>
@endpush

@push('js')
<script>
     // getData
     $('#income-category-table').DataTable({
        processing: true,
        serverSide:true,
        ajax: {
        url: "{{ route('income-category.get-data') }}",
            error: function(xhr, textStatus, errorThrown) {
                $('#income-category-table').DataTable().clear().draw();
                // console.log(xhr.responseText);
                alert('There was an error fetching data. Please try again later.');
            }
        },
        columns: [
            {
                "data": 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {data: 'name', name:'name'},
            {
                data: 'amount',
                render: function(data) {
                    if (data) {
                        return 'Rp. ' + formatRupiah(data);
                    } else {
                        return '-';
                    }
                }
            },
            {data: 'action', name:'action'},
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

    $("div.toolbar").html('<button class="ms-2 btn btn-primary income-category-add" type="button" data-bs-target="#tabs-add-income-category">'+
                            '<span>Create Pemasukan Bonus</span>'+
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>'+
                        '</button>');

    // Event create by Modal
    $(document).on('click', '.income-category-add', function() {
        var getTarget = $(this).data('bs-target');

        $.get("{{ route('income-category.modal-add') }}", function(data) {
            $('#modalContainer').html(data);
            $(`${getTarget}`).modal('show');
            $(`${getTarget}`).on('shown.bs.modal', function () {
                // Multi Select
                var tagsSelect = new TomSelect(".tags-select",{
                    plugins: ['remove_button'],
                });

                $('#select-all-checkbox-tags').change(function() {
                    if(this.checked) {
                        var allOptions = $('.tags-select option').map(function() {
                            return $(this).val();
                        }).get();
                        tagsSelect.setValue(allOptions);
                    } else {
                        tagsSelect.clear();
                    }
                });

                var addonsSelect = new TomSelect(".addons-select",{
                    plugins: ['remove_button'],
                });

                $('#select-all-checkbox-addons').change(function() {
                    if(this.checked) {
                        var allOptions = $('.addons-select option').map(function() {
                            return $(this).val();
                        }).get();
                        addonsSelect.setValue(allOptions);
                    } else {
                        addonsSelect.clear();
                    }
                });
            });
        });

    });

    // Event Edit by Modal
    $(document).on('click', '.income-category-edit-table', function() {
        var productId = $(this).data('bs-target');
        var parts = productId.split("-");
        var id = parseInt(parts[1]);

        $.get("{{ url('income-category/modal-edit') }}/" + id, function(data) {
            $('#modalContainer').html(data);
            $(`${productId}`).modal('show');
            $(`${productId}`).on('shown.bs.modal', function () {
                // Multi Select
                var tagsSelect = new TomSelect(".tags-select",{
                    plugins: ['remove_button'],
                });

                $('#select-all-checkbox-tags').change(function() {
                    if(this.checked) {
                        var allOptions = $('.tags-select option').map(function() {
                            return $(this).val();
                        }).get();
                        tagsSelect.setValue(allOptions);
                    } else {
                        tagsSelect.clear();
                    }
                });

                var addonsSelect = new TomSelect(".addons-select",{
                    plugins: ['remove_button'],
                });

                $('#select-all-checkbox-addons').change(function() {
                    if(this.checked) {
                        var allOptions = $('.addons-select option').map(function() {
                            return $(this).val();
                        }).get();
                        addonsSelect.setValue(allOptions);
                    } else {
                        addonsSelect.clear();
                    }
                });
            });
        });
    });

    // Event Delete by Modal
    $(document).on('click', '.income-category-delete-table', function() {
        var productId = $(this).data('bs-target');
        var parts = productId.split("-");
        var id = parseInt(parts[1]);

        $.get("{{ url('income-category/modal-delete') }}/" + id, function(data) {
            $('#modalContainer').html(data);
            $(`${productId}`).modal('show');
        });
    });

    $('#amount').on('keyup', function() {
        handleInput('amount');
    });

    $(document).on('keyup', '#amount', function(event) {
        if (event.target && event.target.id === 'amount') {
            handleInput('amount');
        }
    });
</script>
@endpush
