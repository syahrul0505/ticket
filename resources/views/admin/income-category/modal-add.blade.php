<div class="modal fade modal-notification" id="tabs-add-income-category" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('income-category.store') }}" method="post" class="modal-content" enctype="multipart/form-data">
        @csrf
        @method('POST')
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="icon-content m-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-inbox"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>
                    </div>
                </div>

                <div class="text-center mb-3 mt-3">
                    <h4 class="mb-0">Tambah Pengeluaran</h4>
                </div>

                <div class="simple-pill">
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-details-icon" role="tabpanel" aria-labelledby="pills-details-icon-tab" tabindex="0">
                            <div class="mt-0 row">
                                
                                <div class="col-12 col-md-6 mb-3">
                                    <div class="form-group mb-3">
                                        <label for="name">name</label>
                                        <input type="text" name="name" min="0" id="name" class="form-control form-control-sm" placeholder="Ex:Cuci Sepatu" aria-label="name">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label for="amount" class="text-white" style="opacity: .8;">Jumlah Harga</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="amount" id="amount" class="form-control form-control-sm" aria-label="Cost Price" placeholder="Ex:10.000" value="{{ old('amount') }}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-dark" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

