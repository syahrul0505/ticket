<div class="modal fade modal-notification" id="tabs-add-additional-income" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('additional-income.store') }}" method="post" class="modal-content" enctype="multipart/form-data">
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
                                
                                <div class="col-12 col-md-6">
                                    <div class="form-group" style="text-align: left">
                                        <label class="form-label">Plih Kategori</label>
                                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" style="width:100%">
                                            <option disabled selected>Pilih Kategori</option>
                                            @foreach ($income_categories as $income_category)
                                                <option value="{{ $income_category->name }}" {{ old('category') == $income_category->id ? 'selected' : '' }}>
                                                    {{ $income_category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <div class="form-group mb-3">
                                        <label for="income_date">Tanggal</label>
                                        <input type="date" name="income_date" min="0" id="income_date" class="form-control form-control-sm" placeholder="Ex:" aria-label="income_date">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <div class="form-group mb-3">
                                        <label for="qty">Jumlah</label>
                                        <input type="number" name="qty" min="1" id="qty" class="form-control form-control-sm" placeholder="Ex:" aria-label="qty">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="description">Deskripsi</label>
                                        <textarea name="description" id="description" cols="30" rows="5" class="form-control" placeholder="Ex:....">{{ old('description') }}</textarea>
            
                                        @if($errors->has('description'))
                                            <p class="text-danger">{{ $errors->first('description') }}</p>
                                        @endif
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

