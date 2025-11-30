

<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/src/tomSelect/tom-select.default.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/light/tomSelect/custom-tomSelect.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/css/dark/tomSelect/custom-tomSelect.css') }}">
<div class="tab-pane fade show active" id="pills-transaction" role="tabpanel" aria-labelledby="pills-transaction-tab" tabindex="0">
    <div class="wrapper">
        <!--start page wrapper -->
		<div class="page-wrapper m-0">
            <div class="page-content">
                @include('admin.components.alert')
				<div class="row">
					<div class="col-12 col-md-6 mb-3">
                        <div class="card">
							<div class="card-body m-0 p-0">
                                <div class="row">
                                    <div class="fm-search col-lg-12 px-4 mt-3">
                                        <div class="mb-0">
                                            <div class="input-group">
                                                {{-- <button class="btn btn-outline-secondary text-dark" type="button" style="font-size:14px;"><i class='bx bx-comment-detail me-0' style="font-size:16px;"></i> <small>Comments</small></button> --}}
                                                <button class="btn btn-success d-flex align-items-center" type="button" style="font-size:14px;" onclick="ModalAddCoupon('{{ route('modal-add-coupon') }}', '{{ route('update-cart-by-coupon') }}', '{{ csrf_token() }}')" data-bs-target="#modal-add-coupon"><i class='bx bx-tag me-1' style="font-size:1.2rem;"></i> <span id="coupon-info">Coupon</span></button>
                                                <input type="text" class="form-control" placeholder="" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
								<!--end row-->
								<form action="{{ route('checkout-order', md5(strtotime("now"))) }}" method="POST" class="">
									@csrf
                                    <input type="hidden" name="coupon_id" value="">
                                    <input type="hidden" name="category" value="{{ $category }}">

									<div class="table-responsive mt-3 custom-height">
										<table class="table table-sm mb-0 custom-table">
											<thead>
												<tr>
													<th width="60%">Product</th>
													<th width="15%">Quantity</th>
													<th width="25%">Harga</th>
												</tr>
											</thead>
											<tbody id="cart-product">
												@forelse ($data_items as $key => $item)
                                                    <tr class="table-cart text-white">
                                                        <td class="td-cart">
                                                            <div class="d-flex justify-content-between">
                                                                <div class="">
                                                                    <p class="p-0 m-0 text-white">
                                                                        {{ $item->name }}
                                                                    </p>
                                                                    {{-- <small>Unit: {{ $item->conditions }}</small> --}}
                                                                </div>

                                                                <div class="">
                                                                    <a href="{{ route('delete-item', $key)}}" class="" style="border-bottom: 1px dashed red;">
                                                                        <i class='bx bx-trash font-14 text-danger'></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="td-cart">
                                                            <a href="#!" type="button" onclick="ModalEditQtyCart('{{ route('modal-edit-qty-cart', $key) }}', '{{ $key }}', '{{ csrf_token() }}')" class="cursor-pointer" data-bs-target="#modal-add-customer" style="border-bottom: 1px dashed #bfbfbf; font-size:12px;">
                                                                <small id="data-qty" style="font-size: 12px;" class="text-white opacity-75">{{ $item->quantity }}</small>
                                                            </a>
                                                        </td>
                                                        <input type="hidden" name="qty[]" id="quantityInput" readonly class="min-width-40 flex-grow-0 border border-success text-success fs-4 fw-semibold form-control text-center qty" min="0" style="width: 15%"  value="{{ $item->quantity }}">
                                                        <td class="td-cart">Rp.{{ number_format($item->price, 0, ',', '.') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr class="table-cart">
                                                        <td colspan="3" class="text-center" style="border-bottom: 1px solid #060818d0">No products added</td>
                                                    </tr>
                                                @endforelse
											</tbody>
										</table>
									</div>

                                    <div class="row my-action align-items-end align-content-end">
                                        <div class="col-12">
                                            <table width="100%" class="table">
                                                <tbody class="info-cart">
                                                    <tr>
                                                        <td style="border-top: 1px solid #060818 !important; border-left: 1px solid #060818 !important;" colspan="2">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-white opacity-75">Sub Total</small>
                                                                <small class="text-white opacity-75" id="subtotal-cart">Rp.{{ number_format($subtotal, 0, ',', '.') }}</small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border-top: 1px solid #060818 !important;">
                                                            <span style="font-size: 12px;" class="text-white opacity-75 d-flex justify-content-between">Pajak({{ $other_setting->pb01 }}%): <small id="tax-cart" style="font-size: 12px;">Rp.{{ number_format($tax, 0, ',', '.') }}</small></span>
                                                        </td>
                                                        <td style="border-top: 1px solid #060818 !important; border-left: 1px solid #060818 !important;" colspan="2">
                                                            <div class="d-flex justify-content-between">
                                                                <span style="font-size: 12px;" class="text-white opacity-75">Discount<small id="type-discount"></small></span>
                                                                <a href="#!" type="button" onclick="ModalAddDiscount('{{ route('modal-add-discount') }}', '{{ route('update-cart-by-discount') }}', '{{ csrf_token() }}')" class="cursor-pointer" data-bs-target="#modal-add-discount" style="border-bottom: 1px dashed #bfbfbf;font-size:14px;">
                                                                    <small id="discount-price" class="text-white opacity-75">Rp.0</small>
                                                                </a>
                                                            </div>
                                                            <input type="hidden" name="type_discount" value="">
                                                            <input type="hidden" name="discount_price" value="">
                                                            <input type="hidden" name="discount_percent" value="">
                                                            <input type="hidden" name="sewa_price" value="">
                                                            <input type="hidden" name="sub_total" id="sub_total_input" value="">
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        @if ($category == 'sewa')
                                                            <td style="border-top: 1px solid #060818 !important; border-bottom: 1px solid #060818 !important;">
                                                                <span style="font-size: 12px;" class="text-white opacity-75 d-flex justify-content-between">Sewa: <small id="sewa-price" class="text-white opacity-75">1 Malam</small> </span>
                                                            </td>
                                                        @endif
                                                        <td class="bg-light-info fw-medium" style="border-top: 1px solid #060818 !important; border-bottom: 1px solid #060818 !important; border-left: 1px solid #060818 !important;" colspan="2">
                                                            <div class="d-flex justify-content-between">
                                                                <small class="text-white opacity-75">Total</small>
                                                                <small id="total-cart" class="text-white opacity-75">Rp.{{ number_format($total, 0, ',', '.') }}</small>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-12">
                                            <div class="btn-group w-100 p-3 pt-0" role="group" aria-label="Grouping Button">
                                                <input type="hidden" name="button" id="buttonValue" value="">
                                                @if ($category == 'jual')

                                                    <button type="button" class="btn btn-lg btn-success fw-bold w-25 p-3" data-bs-toggle="modal" data-bs-target="#modalPayment"  onclick="setButtonValue('simpan-order')">
                                                        <h6 class="mb-0 text-white">
                                                            BUAT PESANAN
                                                        </h6>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-lg btn-warning fw-bold w-25 p-3" data-bs-toggle="modal" data-bs-target="#modalOpenBill" onclick="setButtonValue('simpan-bill')">
                                                        <h6 class="mb-0 text-white">
                                                            BUAT SEWA
                                                        </h6>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-lg btn-primary fw-bold w-25 p-3" onclick="onHoldOrder('{{ route('on-hold-order') }}', '{{ csrf_token() }}')">
                                                    <h6 class="mb-0 text-white">
                                                        ON HOLD
                                                    </h6>
                                                </button>

                                                @if ($category == 'sewa')
                                                    <button type="button" class="btn btn-lg btn-white fw-bold w-25 p-3" onclick="ModalAddSewa('{{ route('modal-add-sewa') }}', '{{ route('update-cart-by-sewa') }}', '{{ csrf_token() }}')" data-bs-target="#modal-add-discount">
                                                        <h6 class="mb-0 text-dark">
                                                            MALAM
                                                        </h6>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-lg btn-secondary fw-bold w-25 p-3" onclick="ModalAddDiscount('{{ route('modal-add-discount') }}', '{{ route('update-cart-by-discount') }}', '{{ csrf_token() }}')" data-bs-target="#modal-add-discount">
                                                    <h6 class="mb-0 text-light">
                                                        DISCOUNT
                                                    </h6>
                                                </button>
                                                <button type="button" class="btn btn-lg btn-danger fw-bold w-25 p-3" onclick="voidCart('{{ route('void-cart') }}', '{{ csrf_token() }}')">
                                                    <h6 class="mb-0 text-white">
                                                        HAPUS SEMUA KERANJANG
                                                    </h6>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
									<div class="modal fade" id="modalPayment" tabindex="-1" aria-labelledby="modalPaymentLabel" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="modalPaymentLabel">PAYMENT</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
                                                    <div class="modal-body p-0">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group mt-2">
                                                                    <h6 class="mb-3">Customer</h6>
                                                                    <select name="customer" id="customer_select_product" class="form-control form-control-sm">
                                                                        <option selected value="new">New</option>
                                                                        <option value="old">Old</option>
                                                                    </select>
                                                                </div>
                                                            </div>
    
                                                            <div class="col-lg-6" id="select_customer_container_product">
                                                                <div class="form-group mt-2">
                                                                    <h6 class="mb-3">Select Customer</h6>
                                                                    <select class="form-select form-select-sm @error('customer_id') is-invalid @enderror" id="customer_id_product" name="customer_id" style="width:100%">
                                                                        <option disabled selected>Select Customer</option>
                                                                        @foreach ($customers as $customer)
                                                                        <option 
                                                                            value="{{ $customer->id }}"
                                                                            data-name="{{ $customer->name }}"
                                                                            data-phone="{{ $customer->phone }}"
                                                                            data-instagram="{{ $customer->instagram }}"
                                                                            data-address="{{ $customer->address }}"
                                                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                                            {{ $customer->name }}
                                                                            ({{ $customer->phone }})
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="form-group mb-2">
                                                            <label for="name_whatsapp_product" class="form-label">Nama Customer</label>
                                                            <input type="text" name="name_whatsapp_product" value="{{ old('name_whatsapp_product') }}" class="form-control form-control-sm" placeholder="Ex:abdul" id="name_whatsapp_product" aria-describedby="name_whatsapp_product">
                                                        </div>
                                                        
                                                        <div class="form-group mb-2">
                                                            <label for="no_wa" class="form-label">No whatsApp <small>(+62)</small></label>
                                                            <input type="text" name="no_wa" value="{{ old('no_wa') }}" class="form-control form-control-sm" placeholder="Ex:62xxxxxxxx" id="no_wa_product" aria-describedby="no_wa">
                                                        </div>

                                                        <div class="form-group">
                                                            <h6 class="mb-3">Metode Payment</h6>
                                                            <select name="payment_method" id="payment_method_product" class="form-control form-control-sm">
                                                                <option selected value="Transfer Bank">Transfer Bank</option>
                                                                <option value="Qris">Qris</option>
                                                                <option value="Cash">Cash</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mt-2" id="cashInputProduct" style="display: none;">
                                                            <label for="cash" class="form-label">Cash</label>
                                                            <input type="text" name="cash" value="{{ old('cash') }}" class="form-control form-control-sm" placeholder="Ex:50.000" id="cash" aria-describedby="cash">
                                                        </div>

                                                    </div>
                                                </div>
												<div class="modal-footer">
													<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CLOSE</button>
                                                    <button type="submit" class="btn btn-primary" onclick="setButtonValue('simpan-order')">BAYAR</button>
												</div>
											</div>
										</div>
									</div>
									<div class="modal fade" id="modalOpenBill" tabindex="-1" aria-labelledby="modalOpenBillLabel" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="modalOpenBillLabel">PAYMENT</h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<div class="modal-body">
                                                    <div class="modal-body p-0">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="form-group mt-2">
                                                                    <h6 class="mb-3">Customer</h6>
                                                                    <select name="customer" id="customer_select" class="form-control form-control-sm">
                                                                        <option selected value="new">New</option>
                                                                        <option value="old">Old</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-6" id="select_customer_container">
                                                                <div class="form-group mt-2">
                                                                    <h6 class="mb-3">Select Customer</h6>
                                                                    <select class="form-select form-select-sm @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" style="width:100%">
                                                                        <option disabled selected>Select Customer</option>
                                                                        @foreach ($customers as $customer)
                                                                        <option 
                                                                            value="{{ $customer->id }}"
                                                                            data-name="{{ $customer->name }}"
                                                                            data-phone="{{ $customer->phone }}"
                                                                            data-instagram="{{ $customer->instagram }}"
                                                                            data-address="{{ $customer->address }}"
                                                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                                                            {{ $customer->name }}
                                                                            ({{ $customer->phone }})
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-lg-6">
                                                                <div class="form-group mt-2">
                                                                    <label for="name_whatsapp" class="form-label">Nama Customer</label>
                                                                    <input type="text" name="name_whatsapp" value="{{ old('name_whatsapp') }}" class="form-control form-control-sm" placeholder="Ex:abdul" id="name_whatsapp" aria-describedby="name_whatsapp">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="col-lg-6">
                                                                <div class="form-group mt-2">
                                                                    <label for="no_wa" class="form-label">No WhatsApp <small>(+62)</small></label>
                                                                    <input type="text" name="no_wa" value="{{ old('no_wa') }}" class="form-control form-control-sm" placeholder="Ex:62xxxxxxxx" id="no_wa" aria-describedby="no_wa">
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-6">
                                                                <div class="form-group">
                                                                    <h6 class="mt-3">Status Order</h6>
                                                                    <select name="status_order" id="status_order" class="form-control form-control-sm">
                                                                        <option selected value="Order Baru">Order Baru</option>
                                                                        <option value="Order Tambahan">Order Tambahan</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-lg-12" id="invoice-container" style="display: none;">
                                                                <div class="form-group">
                                                                    <h6 class="mt-2">Order</h6>
                                                                    <select class="form-select form-select-sm @error('no_invoice') is-invalid @enderror" id="no_invoice" name="no_invoice" style="width:100%" disabled>
                                                                        <option disabled selected>Select No Invoice</option>
                                                                        @foreach ($orders as $order)
                                                                            <option 
                                                                                value="{{ $order->no_invoice }}"
                                                                                {{ old('no_invoice') == $order->id ? 'selected' : '' }}>
                                                                                {{ $order->no_invoice }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            
                                                        </div>  

                                                        <div class="form-group mb-2 mt-2">
                                                            <label for="instagram" class="form-label">Instagram</label>
                                                            <input type="text" name="instagram" value="{{ old('instagram') }}" class="form-control form-control-sm" placeholder="Ex:xxxxxxx" id="instagram" aria-describedby="instagram">
                                                        </div>

                                                        <div class="form-group">
                                                            <h6 class="mb-3">Jaminan</h6>
                                                            <select name="guarantee" id="guarantee" class="form-control form-control-sm">
                                                                <option selected value="E-KTP">E-KTP</option>
                                                                <option value="Kartu Pelajar">Kartu Pelajar</option>
                                                                <option value="KTM">KTM</option>
                                                                <option value="SIM">SIM</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <h6 class="mb-3">Metode Payment</h6>
                                                            <select name="payment_method" id="payment_method_sewa" class="form-control form-control-sm">
                                                                <option selected value="Transfer Bank">Transfer Bank</option>
                                                                <option value="Qris">Qris</option>
                                                                <option value="Cash">Cash</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mt-2" id="cashInputSewa" style="display: none;">
                                                            <label for="cash" class="form-label">Cash</label>
                                                            <input type="text" name="cash" value="{{ old('cash') }}" class="form-control form-control-sm" placeholder="Ex:50.000" id="cash" aria-describedby="cash">
                                                        </div>

                                                        <div class="form-group mt-2">
                                                            <label for="start_date" class="form-label">Start Date</label>
                                                            <input type="datetime-local" name="start_date" value="{{ old('start_date') }}" class="form-control form-control-sm" placeholder="Ex:" id="start_date" aria-describedby="start_date">
                                                        </div>

                                                        <div class="form-group mt-2">
                                                            <label for="end_date" class="form-label">End Date</label>
                                                            <input type="datetime-local" name="end_date" value="{{ old('end_date') }}" class="form-control form-control-sm" placeholder="Ex:" id="end_date" aria-describedby="end_date">
                                                        </div>

                                                        <div class="col-12 mt-2">
                                                            <div class="form-group mb-3">
                                                                <label for="address">Address</label>
                                                                <textarea name="address" id="address" cols="30" rows="5" class="form-control" placeholder="Ex:Jl.sudirman">{{ old('address') }}</textarea>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
												<div class="modal-footer">
													<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CLOSE</button>
                                                    <button type="submit" class="btn btn-primary" onclick="setButtonValue('simpan-bill')">SUBMIT</button>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
                    </div>
					<div class="col-12 col-md-6">
                        <div class="card">
							<div class="card-body m-0 p-0">
                                <div class="row">
                                    <div class="fm-search col-lg-12 px-4 mt-3">
                                        <div class="mb-0">
                                            <di v class="input-group">
                                                {{-- <button class="btn btn-outline-info text-white d-flex align-items-center" type="button">Favorites</button>
                                                <button class="btn btn-outline-info text-white d-flex align-items-center" type="button">All Menu</button> --}}
                                                <input type="text" id="text-search" class="form-control barcode" placeholder="" disabled>
                                                <button class="btn btn-primary text-white d-flex align-items-center" type="button" onclick="ModalSearch('{{ route('modal-search-product') }}', '{{ route('search-product') }}', '{{ csrf_token() }}' ,'{{ $category }}')"><i class='bx bx-search-alt me-0' style="font-size: 1.2rem !important;"></i></button>
                                            </di>
                                        </div>
                                    </div>
                                </div>
								<!--end row-->
								<div class="row mt-3">
                                    <div class="col-12">
                                        <ul class="list-group">
                                            <li class="list-group-item d-flex justify-content-start align-items-center gap-1 text-white" style="background-color: #060818 !important; border:none; border-radius:10px !important; opacity: 0.9;">
                                                <a href="#!" onclick="loadTags('{{ route('get-tag') }}');">Home</a> /
                                                <span id="tagNavigation"></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

								<div class="row mt-3 px-3 pb-3 my-product align-content-start" id="productContainer"></div>
							</div>
						</div>
                    </div>
				</div>
				<!--end row-->
			</div>
		</div>
		<!--end page wrapper -->
	</div>
</div>

<script src="{{ asset('src/plugins/src/tomSelect/tom-select.base.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const customerSelect = document.getElementById("customer_select");
        const customerContainer = document.getElementById("select_customer_container");
        const inputName = document.getElementById("name_whatsapp");
        const inputWa = document.getElementById("no_wa");
        const inputInstagram = document.getElementById("instagram");
        const inputAddress = document.getElementById("address");

        // Inisialisasi TomSelect
        const tomSelectInstance = new TomSelect("#customer_id", {
            maxItems: 1,
            create: false,
            persist: false,
            placeholder: "Select Customer",
            onChange: function(value) {
                const selectedOption = document.querySelector(`#customer_id option[value="${value}"]`);
                if (selectedOption) {
                    inputName.value = selectedOption.getAttribute("data-name") || "";
                    inputWa.value = selectedOption.getAttribute("data-phone") || "";
                    inputInstagram.value = selectedOption.getAttribute("data-instagram") || "";
                    inputAddress.value = selectedOption.getAttribute("data-address") || "";
                }
            }
        });

        // Fungsi toggle form berdasarkan new/old
        function toggleCustomerFields() {
            if (customerSelect.value === "new") {
                customerContainer.style.display = "none";
                tomSelectInstance.clear(); // reset pilihan TomSelect
                inputName.value = "";
                inputWa.value = "";
            } else {
                customerContainer.style.display = "block";
            }
        }

        // Event saat customer (new/old) dipilih
        customerSelect.addEventListener("change", toggleCustomerFields);

        // Jalankan saat pertama load
        toggleCustomerFields();
    });
    
</script>   

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const customerSelect = document.getElementById("customer_select_product");
        const customerContainer = document.getElementById("select_customer_container_product");
        const inputName = document.getElementById("name_whatsapp_product");
        const inputWa = document.getElementById("no_wa_product");

        // Inisialisasi TomSelect
        const tomSelectInstance = new TomSelect("#customer_id_product", {
            maxItems: 1,
            create: false,
            persist: false,
            placeholder: "Select Customer",
            onChange: function(value) {
                const selectedOption = document.querySelector(`#customer_id_product option[value="${value}"]`);
                if (selectedOption) {
                    inputName.value = selectedOption.getAttribute("data-name") || "";
                    inputWa.value = selectedOption.getAttribute("data-phone") || "";
                    inputInstagram.value = selectedOption.getAttribute("data-instagram") || "";
                    inputAddress.value = selectedOption.getAttribute("data-address") || "";
                }
            }
        });

        // Fungsi toggle form berdasarkan new/old
        function toggleCustomerFields() {
            if (customerSelect.value === "new") {
                customerContainer.style.display = "none";
                tomSelectInstance.clear(); // reset pilihan TomSelect
                inputName.value = "";
                inputWa.value = "";
            } else {
                customerContainer.style.display = "block";
            }
        }

        // Event saat customer (new/old) dipilih
        customerSelect.addEventListener("change", toggleCustomerFields);

        // Jalankan saat pertama load
        toggleCustomerFields();
    });
    
</script>   

<script>
    const selectInput = document.getElementById('payment_method_product');
    const cashInput = document.getElementById('cashInputProduct');

    selectInput.addEventListener('change', function() {
        if (selectInput.value === 'Cash') {
            cashInput.style.display = 'block';
        } else {
            cashInput.style.display = 'none';
        }
    });

</script>

<script>
    const selectInputSewa = document.getElementById('payment_method_sewa');
    const cashInputSewa = document.getElementById('cashInputSewa');

    selectInputSewa.addEventListener('change', function() {
        if (selectInputSewa.value === 'Cash') {
            cashInputSewa.style.display = 'block';
        } else {
            cashInputSewa.style.display = 'none';
        }
    });
</script>

