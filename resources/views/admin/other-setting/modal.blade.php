<div class="modal fade modal-notification" id="tabs-other-setting" tabindex="-1" role="dialog" aria-labelledby="tabsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="mt-0 modal-content" action="{{ route('other-settings.update', ($other_setting->id ?? 0)) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="icon-content m-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    </div>
                </div>

                <div class="text-center mb-3 mt-3">
                    <h4 class="mb-0">EDIT OTHER SETTING</h4>
                </div>


                <div class="mt-0 row">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="pb01">Pajak</label>
                        <div class="input-group">
                            <input type="number" name="pb01" class="form-control form-control-sm" placeholder="Ex:11" aria-label="pb01" id="pb01" min="0" max="100" value="{{ $other_setting->pb01 ?? old('pb01') }}">
                            <span class="input-group-text">%</span>

                            @if($errors->has('pb01'))
                                <p class="text-danger">{{ $errors->first('pb01') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="layanan">Layanan</label>
                        <div class="input-group">
                            <input type="text" name="layanan" class="form-control form-control-sm" placeholder="Ex:5" aria-label="layanan" id="layanan" min="0" max="100" value="{{ $other_setting->layanan ?? old('layanan') }}">
                            <span class="input-group-text">%</span>

                            @if($errors->has('layanan'))
                                <p class="text-danger">{{ $errors->first('layanan') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="regular_day_salary">Gaji Hari Biasa</label>
                        <div class="input-group">
                            <input type="text" name="regular_day_salary" class="form-control form-control-sm" placeholder="Ex:5" aria-label="regular_day_salary" id="regular_day_salary" value="{{ $other_setting->regular_day_salary ?? old('regular_day_salary') }}">
                            <span class="input-group-text">menit</span>

                            @if($errors->has('regular_day_salary'))
                                <p class="text-danger">{{ $errors->first('regular_day_salary') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="holiday_salary">Gaji Hari Libur</label>
                        <div class="input-group">
                            <input type="text" name="holiday_salary" class="form-control form-control-sm" placeholder="Ex:5" aria-label="holiday_salary" id="holiday_salary" value="{{ $other_setting->holiday_salary ?? old('holiday_salary') }}">
                            <span class="input-group-text">menit</span>

                            @if($errors->has('holiday_salary'))
                                <p class="text-danger">{{ $errors->first('holiday_salary') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-12">
                        <div class="form-group mb-3 text-left">
                            <label for="logo">Logo</label>
                            <img src="{{ $other_setting->logo ? asset('images/products/'.$other_setting->logo) : 'https://ui-avatars.com/api/?name=No+Image' }}" alt="" class="d-block mx-auto p-1 bg-black mb-2" style="width: 73px !important; border-radius:50%;">
                            <input type="file" class="form-control file-upload-input" name="logo" aria-label="logo" id="logo">

                            @if($errors->has('logo'))
                                <p class="text-danger">{{ $errors->first('logo') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="layanan">Nama Brand</label>
                        <div class="input-group">
                            <input type="text" name="name_brand" class="form-control form-control-sm" placeholder="Ex:5" aria-label="name_brand" id="name_brand" min="0" max="100" value="{{ $other_setting->name_brand ?? old('name_brand') }}">

                            @if($errors->has('name_brand'))
                                <p class="text-danger">{{ $errors->first('name_brand') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 mb-3">
                        <label for="layanan">End Struk</label>
                        <div class="input-group">
                            <input type="text" name="name_footer" class="form-control form-control-sm" placeholder="Ex:Selamat Kasih atas Kunjungan Anda" aria-label="name_footer" id="name_footer" min="0" max="100" value="{{ $other_setting->name_footer ?? old('name_footer') }}">
                            
                            @if($errors->has('name_footer'))
                            <p class="text-danger">{{ $errors->first('name_footer') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-12 col-md-6 mb-3">
                        <label for="layanan">End Struk Product</label>
                        <div class="input-group">
                            <input type="text" name="name_footer_product" class="form-control form-control-sm" placeholder="Ex:Selamat Kasih atas Kunjungan Anda" aria-label="name_footer_product" id="name_footer_product" min="0" max="100" value="{{ $other_setting->name_footer_product ?? old('name_footer_product') }}">
                            
                            @if($errors->has('name_footer_product'))
                            <p class="text-danger">{{ $errors->first('name_footer_product') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <div class="form-group mb-3">
                            <label for="address">Alamat</label>
                            <textarea name="address" id="address" cols="30" rows="5" class="form-control" placeholder="Ex:Jl.sudirman">{{ $other_setting->address ?? old('address') }}</textarea>
                            
                            @if($errors->has('address'))
                            <p class="text-danger">{{ $errors->first('address') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-12 mb-3">
                        <div class="form-group mb-3">
                            <label for="second_address">Second Address</label>
                            <textarea name="second_address" id="second_address" cols="30" rows="5" class="form-control" placeholder="Ex:Jl.sudirman">{{ $other_setting->second_address ?? old('second_address') }}</textarea>
                            
                            @if($errors->has('second_address'))
                            <p class="text-danger">{{ $errors->first('second_address') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="time_start">Open Outlet</label>
                            <input type="time" name="time_start" class="form-control form-control-sm" aria-label="time_start" id="time_start" value="{{ date('H:i', strtotime($other_setting->time_start)) }}">

                            @if($errors->has('time_start'))
                                <p class="text-danger">{{ $errors->first('time_start') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group mb-3">
                            <label for="time_close">Close Outlet</label>
                            <input type="time" name="time_close" class="form-control form-control-sm" aria-label="time_close" id="time_close" value="{{ date('H:i', strtotime($other_setting->time_close)) }}">

                            @if($errors->has('time_close'))
                                <p class="text-danger">{{ $errors->first('time_close') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light-dark" type="button" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

