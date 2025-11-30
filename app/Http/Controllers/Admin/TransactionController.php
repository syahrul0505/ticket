<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\CacheOnholdControl;
use App\Models\Coupons;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderProductAddon;
use App\Models\OtherSetting;
use App\Models\Product;
use App\Models\ProductTag;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Cache;
use PDF;

class TransactionController extends Controller
{
    public function index(Request $request)
    {

        if ($request->category == null) {
            return redirect()->route('jual-sewa')->with('failed', 'Pilih Sewa Atau Beli!');
        }

        $data['page_title'] = 'Transaction';
        $data['data_items'] = Cart::session(Auth::user()->id)->getContent();
        $data['products'] = Product::where('category', $request->category)->orderBy('id', 'asc')->get();
        $data['customers'] = Customer::orderBy('name', 'asc')->get();
        $data['other_setting'] = OtherSetting::get()->first();
        $data['orders'] = Order::orderBy('no_invoice', 'desc')->where('payment_status', 'Paid')->where('status_product', 'Sewa')->whereDate('created_at', Carbon::today())->get();
        $data['category'] = $request->category;
        $service = $data['other_setting']->layanan / 100;
        $subtotal = Cart::getTotal();
        // dd($data['data_items']);
        $data['subtotal'] = $subtotal;
        $data['service'] = $subtotal * $service;
        $data['tax'] = (($data['subtotal'] + ($data['data_items']->isEmpty() ? 0 : $data['service'])) * $data['other_setting']->pb01 / 100);
        $data['total'] = ($data['subtotal'] + ($data['data_items']->isEmpty() ? 0 : $data['service'])) + $data['tax'];

        $data['category'];
        $request->category;

        return view('admin.pos.index', $data);
    }

    public function jualSewa()
    {
        $data['page_title'] = 'Transaction';

        return view('admin.pos.jual-sewa', $data);
    }

    // ========================================================================================
    // Modal View
    // ========================================================================================

    // Modal add Discount
    public function modalAddDiscount()
    {
        return View::make('admin.pos.modal.modal-add-discount');
    }

    // Modal add Sewa
    public function modalAddSewa()
    {
        return View::make('admin.pos.modal.modal-add-sewa');
    }

    // Modal add Coupon
    public function modalAddCoupon()
    {
        Cart::session(Auth::user()->id)->getContent();
        $subtotal = Cart::getTotal();

        $coupons = Coupons::where('minimum_cart', '<=', $subtotal)
            ->where('expired_at', '>=', now())
            ->whereRaw('current_usage < limit_usage')
            ->get();

        return View::make('admin.pos.modal.modal-add-coupon')->with([
            'coupons' => $coupons,
        ]);
    }

    // Modal add Customer
    public function modalAddCustomer()
    {
        return View::make('admin.pos.modal.modal-add-customer');
    }

    // Modal Search
    public function modalSearchProduct()
    {
        return View::make('admin.pos.modal.modal-search-product');
    }

    public function modalEditQtyCart($key)
    {
        // Ambil item dari cart berdasarkan key
        $cartItem = Cart::session(Auth::user()->id)->get($key);

        if (!$cartItem) {
            return response()->json(['failed' => 'Cart item not found!'], 404);
        }

        return View::make('admin.pos.modal.modal-edit-qty-cart')->with([
            'key' => $key,
            'quantity' => $cartItem->quantity, // Passing quantity ke view
        ]);
    }

    // Modal My Order
    public function modalMyOrder()
    {
        $today = Carbon::today();
        $getOrderPaid = Order::wherePaymentStatus('Paid')->whereDate('created_at', $today)->orderBy('id', 'desc')->get();
        $getOrderOpenBill = Order::wherePaymentStatus('Unpaid')->whereDate('created_at', $today)->orderBy('id', 'desc')->get();
        $getCacheOnhold = CacheOnholdControl::select(['key', 'name'])->whereDate('created_at', $today)->orderBy('id', 'desc')->get();

        return View::make('admin.pos.modal.modal-my-order')->with([
            'order_paids' => $getOrderPaid,
            'order_open_bills' => $getOrderOpenBill,
            'onhold_orders' => $getCacheOnhold,
        ]);
    }

    // Modal Add Cart
    public function modalAddCart(Request $request, $productId)
    {
        $productById = Product::with('addons')->findOrFail($productId);
        $addons = $productById->addons;

        $parentAddons = $addons->where('parent_id', null);
        $childAddons = Addon::where('parent_id', '!=', null)->get();

        $structuredAddons = [];
        foreach ($parentAddons as $parentAddon) {
            // Tambahkan data parent addon ke array hasil
            $structuredAddons[$parentAddon->id] = [
                'addon' => $parentAddon,
                'children' => []
            ];
        }

        foreach ($childAddons as $childAddon) {
            if (isset($structuredAddons[$childAddon->parent_id])) {
                $structuredAddons[$childAddon->parent_id]['children'][] = $childAddon;
            }
        }

        $formattedAddons = [];

        foreach ($structuredAddons as $structuredAddon) {
            $formattedAddons[] = [
                'addon' => $structuredAddon['addon'],
                'children' => $structuredAddon['children']
            ];
        }

        return View::make('admin.pos.modal.modal-add-cart')->with([
            'product' => $productById,
            'addons' => $formattedAddons
        ]);
    }

    // Add Ongkir
    // public function modalAddOngkir()
    // {
    //     return View::make('pos.modal-add-ongkir');
    // }

    // ========================================================================================
    // End Modal View
    // ========================================================================================


    // ========================================================================================
    // Other Function
    // ========================================================================================

    // Get Data Tag
    public function getTag()
    {
        $allTag = Tag::has('products')->get();
        return response()->json($allTag, 200);
    }

    public function getProduct($idTag)
    {
        $category = request()->query('category'); // Get the 'category' parameter from the request URL

        $getProductByTags = Product::whereHas('productTag', function ($query) use ($idTag, $category) {
            $query->where('tag_id', $idTag)->where('category', $category);
        })->get();

        return response()->json($getProductByTags, 200);
    }


    public function deleteItem($id)
    {
        if (Auth::check()) {
            Cart::session(Auth::user()->id)->remove($id);
        }
        $user = 'guest';
        Cart::session($user)->remove($id);
        return redirect()->back()->with('success', 'Item deleted successfully!');
    }

    public function addToCart(Request $request)
    {
        try {
            if ($request->product_id == null) {
                return redirect()->back()->with('failed', 'Please Select The Product!');
            }

            $product = Product::findOrFail($request->product_id);

            // Ambil addons dari request
            $addons = $request->addons ?? [];

            // Perhitungan harga diskon
            $priceForPercent = $product->selling_price ?? 0;
            $priceAfterDiscount = $priceForPercent;

            if ($product->is_discount) {
                if ($product->price_discount && $product->price_discount > 0) {
                    $priceAfterDiscount = $product->price_discount;
                } elseif ($product->percent_discount && $product->percent_discount > 0 && $product->percent_discount <= 100) {
                    $discount_price = $priceForPercent * ($product->percent_discount / 100);
                    $priceAfterDiscount = $priceForPercent - $discount_price;
                }
            }

            // Hitung total harga addons
            $totalAddonPrice = array_reduce($addons, function ($carry, $addon) {
                return $carry + $addon['price'];
            }, 0);

            // Tambahkan harga addons ke harga produk
            $totalPrice = $priceAfterDiscount + $totalAddonPrice;

            // Siapkan atribut detail produk
            $productDetailAttributes = array(
                'product' => $product,
                'addons' => $addons,
            );

            $itemIdentifier = md5(json_encode($productDetailAttributes));

            $cartContent = Cart::session(Auth::user()->id)->getContent();

            // Cek apakah item yang akan ditambahkan sudah ada di keranjang
            $existingItem = $cartContent->first(function ($item, $key) use ($productDetailAttributes) {
                $attributes = $item->attributes;

                // Periksa apakah produk dan addons sama dengan yang ada dalam keranjang
                if (
                    $attributes['product']['id'] === $productDetailAttributes['product']['id'] &&
                    $attributes['addons'] == $productDetailAttributes['addons']
                ) {
                    return true;
                }

                return false;
            });

            if ($existingItem !== null) {
                // Jika item sudah ada, tambahkan jumlahnya
                Cart::session(Auth::user()->id)->update($existingItem->id, [
                    'quantity' => $request->quantity,
                    'attributes' => $existingItem->attributes->toArray(),
                ]);
            } else {
                // Jika item belum ada, tambahkan ke keranjang
                Cart::session(Auth::user()->id)->add(array(
                    'id' => $itemIdentifier,
                    'name' => $product->name,
                    'price' => $totalPrice,
                    'quantity' => $request->quantity,
                    'attributes' => $productDetailAttributes,
                    'associatedModel' => Product::class
                ));
            }

            $other_setting = OtherSetting::select(['pb01', 'layanan'])->first();
            $subtotal = (Cart::getTotal() ?? '0');
            $service = $subtotal * ($other_setting->layanan / 100);
            $tax = (($subtotal + $service) * $other_setting->pb01 / 100);
            $totalPayment = ($subtotal + $service) + $tax;

            return response()->json([
                'success' => 'Product ' . $product->name . ' Berhasil masuk cart!',
                'data' => Cart::session(Auth::user()->id)->getContent()->toArray(),
                'service' => $service,
                'tax' => $tax,
                'subtotal' => $subtotal,
                'total' => $totalPayment,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['failed' => 'Product ' . $product->name . ' gagal masuk cart!' . $th->getMessage()], 500);
        }
    }

    // Update Qty
    public function updateCartQuantity(Request $request)
    {
        try {
            $cartItemKey = $request->key;
            $newQuantity = $request->quantity;

            // Cek apakah key dan quantity diberikan
            if ($cartItemKey == null || $newQuantity == null) {
                return response()->json(['failed' => 'Please provide a valid cart item key and quantity!'], 400);
            }

            // Cek apakah item dengan key tersebut ada di dalam cart
            $cartItem = Cart::session(Auth::user()->id)->get($cartItemKey);

            if ($cartItem) {
                // Update quantity dengan mengatur secara absolut ke nilai baru
                Cart::session(Auth::user()->id)->update($cartItemKey, [
                    'quantity' => [
                        'relative' => false,
                        'value' => $newQuantity
                    ],
                ]);

                $other_setting = OtherSetting::select(['pb01', 'layanan'])->first();
                $subtotal = (Cart::getTotal() ?? '0');
                $service = $subtotal * ($other_setting->layanan / 100);
                $tax = ($subtotal * $other_setting->pb01 / 100);
                $totalPayment = ($subtotal + $service) + $tax;

                $canDelete = Auth::user()->can('delete-product-in-cart');

                return response()->json([
                    'success' => 'Cart item updated successfully!',
                    'data' => Cart::session(Auth::user()->id)->getContent()->toArray(),
                    'service' => $service,
                    'tax' => $tax,
                    'subtotal' => $subtotal,
                    'total' => $totalPayment,
                    'canDelete' => $canDelete,
                ], 200);
            } else {
                return response()->json(['failed' => 'Cart item not found!'], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['failed' => 'Failed to update cart item! ' . $th->getMessage()], 500);
        }
    }

    // Void Cart
    public function voidCart()
    {
        Cart::session(Auth::user()->id)->clear();
        return redirect()->back()->with('success', 'Cart berhasil dibersihkan!');
    }

    public function getDataCustomers(Request $request)
    {
        $customers = Customer::select(['id', 'name'])->get();
        return response()->json($customers);
    }

    // ====================================================
    // Update Cart By Coupon
    public function updateCartByCoupon(Request $request)
    {
        Cart::session(Auth::user()->id)->getContent();
        $coupon = Coupons::findOrFail($request->coupon_id);
        $coupon_type = $coupon->type;
        $subtotal = Cart::getTotal();
        $other_setting = OtherSetting::get()->first();
        $service = $other_setting->layanan / 100;
        $biaya_layanan = 0;

        // Calculate discount amount based on coupon type
        if ($coupon_type == 'Percentage Discount') {
            $coupon_amount = $subtotal * $coupon->discount_value / 100;

            // Apply max discount value if applicable
            if ($subtotal >= $coupon->discount_threshold && $coupon_amount > $coupon->max_discount_value) {
                $coupon_amount = $coupon->max_discount_value;
            }
        } else {
            $coupon_amount = (int) $coupon->discount_value;
        }

        // Check Layanan
        if ($other_setting->layanan != 0) {
            $biaya_layanan = ($subtotal - $coupon_amount) * $service;
            $temp_total = $subtotal + $biaya_layanan;
        } else {
            $temp_total = (($subtotal - $coupon_amount) ?? 0);
        }

        // Update tax & total price
        $tax = $temp_total * ($other_setting->pb01 / 100);
        $total = $temp_total + ($tax);
        $info = $coupon->name;

        return response()->json([
            'success' => 'Coupon ' . $coupon->name . ' berhasil ditambahkan!',
            'coupon_type' => $coupon_type,
            'coupon_amount' => $coupon_amount,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'service' => $biaya_layanan,
            'info' => $info,
        ], 200);
    }

    // Update Cart By Discount
    public function updateCartByDiscount(Request $request)
    {
        Cart::session(Auth::user()->id)->getContent();
        $other_setting = OtherSetting::first();

        // Mengambil nilai dari request
        $discount_price = (int) str_replace('.', '', $request->discount_price);
        $discount_percent = (int) ($request->discount_percent ?? 0);
        $discount_type = $request->discount_type;
        $sewa = (int) filter_var($request->sewa, FILTER_SANITIZE_NUMBER_INT) ?: 1;

        //  ambil subtotal asli dari Cart 
        $subtotal_asli = Cart::getTotal();
        $subtotal_dikali_sewa = $subtotal_asli * $sewa;
        //pake tipe
        $discount_amount = 0;
        if ($discount_type == 'percent') {
            $discount_amount = $subtotal_dikali_sewa * $discount_percent / 100;
        } else {
            $discount_amount = $discount_price;
        }

        // Pastikan diskon tidak lebih besar dari total
        $discount_amount = min($discount_amount, $subtotal_dikali_sewa);

        $subtotal_setelah_diskon = $subtotal_dikali_sewa - $discount_amount;

        $biaya_layanan = 0;
        if ($other_setting->layanan > 0) {
            $service_rate = $other_setting->layanan / 100;
            $biaya_layanan = $subtotal_setelah_diskon * $service_rate;
        }

        $total_sebelum_pajak = $subtotal_setelah_diskon + $biaya_layanan;

        $tax = 0;
        if ($other_setting->pb01 > 0) {
            $tax_rate = $other_setting->pb01 / 100;
            $tax = $total_sebelum_pajak * $tax_rate;
        }
        $final_total = $total_sebelum_pajak + $tax;

        return response()->json([
            'success' => 'Discount berhasil diperbarui!',
            'discount_price' => $discount_price,
            'discount_percent' => $discount_percent,
            'discount_type' => $discount_type,
            'discount_amount' => $discount_amount,
            'service' => $biaya_layanan,
            'sewa' => $sewa,
            'subtotal' => $subtotal_dikali_sewa,
            'tax' => $tax,
            'total' => $final_total,
        ], 200);
    }



    // Update Cart By Sewa
    public function updateCartBySewa(Request $request)
    {
        $cartTotal = Cart::session(Auth::user()->id)->getTotal();
        $other_setting = OtherSetting::first();

        $discount_price = (int) str_replace('.', '', $request->discount_price);
        $discount_percent = (int) $request->discount_percent;
        $discount_type = $request->discount_type;
        $service = $other_setting->layanan / 100;
        $biaya_layanan = 0;

        // Pastikan sub_total tidak terpengaruh dua kali oleh sewa_price
        $sub_total_awal = (int) str_replace(['Rp.', '.'], '', $request->sub_total);
        $sewa = (int) $request->sewa_price;
        $sub_total = $sub_total_awal * $sewa; // Dikalikan dengan sewa hanya saat ini

        // Perhitungan diskon
        if ($discount_type == 'percent') {
            $discount_amount = $sub_total * $discount_percent / 100;
        } else {
            $discount_amount = $discount_price;
        }

        // Perhitungan biaya layanan
        if ($other_setting->layanan > 0) {
            $biaya_layanan = max(0, ($sub_total - $discount_amount) * $service);
        }

        $temp_total = max(0, ($sub_total - $discount_amount) + $biaya_layanan);

        // Perhitungan pajak
        $tax = $temp_total * ($other_setting->pb01 / 100);
        $total = $temp_total + $tax;

        return response()->json([
            'success' => 'Discount berhasil ditambahkan!',
            'discount_price' => $discount_price,
            'discount_percent' => $discount_percent,
            'discount_type' => $discount_type,
            'discount_amount' => $discount_amount,
            'service' => $biaya_layanan,
            'sewa_price' => $sewa,
            'subtotal' => $sub_total_awal, // Menampilkan sub total awal sebelum dikalikan
            'subtotal_final' => $sub_total, // Menampilkan sub total setelah dikalikan dengan sewa
            'tax' => $tax,
            'total' => $total,
        ], 200);
    }


    public function searchProduct(Request $request)
    {
        $products = Product::select(['id', 'name'])->where('category', $request->category)->get();
        return response()->json($products);
    }

    // On Hold
    public function onHoldOrder(Request $request)
    {
        try {

            // Get All Session Cart
            $session_cart = Cart::session(Auth::user()->id)->getContent()->toArray();

            // Create unique key
            $uniqueKey = uniqid();

            // Simpan data session cart ke Cache File dengan uniqeuKey
            Cache::put('onHoldCart:user:' . Auth::user()->id . ':' . $uniqueKey, $session_cart, 86400);

            $dataCache = CacheOnholdControl::create([
                'key' => $uniqueKey,
                'name' => ($request->name ? $request->name : 'No Name')
            ]);

            // Clear session cart
            if ($dataCache) {
                Cart::session(Auth::user()->id)->clear();
            }

            return response()->json([
                'code' => 200,
                'message' => 'Order telah berhasil disimpan.',
            ], 200);

        } catch (\Throwable $th) {
            // Tangani kesalahan jika terjadi
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function openOnholdOrder(Request $request)
    {
        try {
            $other_setting = OtherSetting::get()->first();

            Cart::session(Auth::user()->id)->clear();
            $keyCache = 'onHoldCart:user:' . Auth::user()->id . ':' . $request->key;

            if (Cache::has($keyCache)) {
                // Get Cache by key
                $getCache = Cache::get($keyCache);

                // Add data to cart
                foreach ($getCache as $cache) {
                    dd($cache['attributes']);
                    Cart::session(Auth::user()->id)->add([
                        'id' => $cache['id'],
                        'name' => $cache['name'],
                        'price' => $cache['price'],
                        'quantity' => $cache['quantity'],
                        'attributes' => $cache['attributes'],
                        'conditions' => $cache['conditions'],
                    ]);
                }

                // Delete Cache after add to cart
                Cache::forget($keyCache);
                CacheOnholdControl::where('key', $request->key)->delete();

                // Set return data
                $dataCart = Cart::session(Auth::user()->id)->getContent();
                $subtotal = Cart::getTotal();
                $service = $subtotal * ($other_setting->layanan / 100);
                $tax = ($subtotal + $service) * ($other_setting->pb01 / 100);
                $total_price = ($subtotal + $service) + $tax;


                return response()->json([
                    'code' => 200,
                    'message' => 'Open onhold Berhasil.',
                    'data' => $dataCart,
                    'service' => $service,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total_price,
                ], 200);
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    //  Open Bill
    public function openBillOrder(Request $request)
    {
        try {
            $other_setting = OtherSetting::first();

            Cart::session(Auth::user()->id)->clear();

            $order = Order::where('id', $request->id)->first(); // Menggunakan first() untuk mengambil satu objek
            $orderProducts = OrderProduct::where('order_id', $order->id)->get();


            // Add data to cart
            foreach ($orderProducts as $orderProduct) {
                $products = Product::where('name', $orderProduct->name)->first();
                $orderAddOns = OrderProductAddon::where('order_product_id', $orderProduct->id)->first();

                Cart::session(Auth::user()->id)->add([
                    'id' => $orderProduct->id,
                    'name' => $orderProduct->name,
                    'price' => $orderProduct->selling_price,
                    'quantity' => $orderProduct->qty,
                    'attributes' => [
                        'product' => $products,
                        'addons' => $orderAddOns ?? [],
                    ],
                ]);
            }

            // Delete Cache after add to cart
            $orders = Order::findOrFail($request->id);
            $orders->delete();

            // Set return data
            $dataCart = Cart::session(Auth::user()->id)->getContent();
            $subtotal = Cart::getTotal();
            $service = $subtotal * ($other_setting->layanan / 100);
            $tax = ($subtotal + $service) * ($other_setting->pb01 / 100);
            $total_price = ($subtotal + $service) + $tax;

            return response()->json([
                'code' => 200,
                'message' => 'Open Bill Berhasil.',
                'data' => $dataCart,
                'service' => $service,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total_price,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    public function deleteOnholdOrder(Request $request)
    {
        try {
            $keyCache = 'onHoldCart:user:' . Auth::user()->id . ':' . $request->key;

            // Delete Cache after add to cart
            CacheOnholdControl::where('key', $request->key)->delete();
            Cache::forget($keyCache);

            return response()->json([
                'code' => 200,
                'message' => 'Delete onhold Berhasil.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function printCustomer($id)
    {
        $orders = Order::findOrFail($id);

        try {
            $this->printItems($orders, 'food');
            $this->printItems($orders, 'drink');

            return redirect()->back()->with('success', 'Print berhasil dilakukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('failed', 'Gagal melakukan print! Error: ' . $e->getMessage());
        }
    }

    public function printItems($orders, $category)
    {
        $connector = new NetworkPrintConnector("192.168.123.120", 9100);
        $printer = new Printer($connector);

        /* Initialize */
        $printer->initialize();

        $printer->initialize();
        $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        // Print store name
        $printer->text("A2 Coffee & Eatry \n");
        $printer->text("\n");

        // Print store address
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(",  \n");
        $printer->text("Jawa Barat, 17530\n");
        $printer->text("\n\n");

        // Print transaction details
        $printer->initialize();
        $printer->text("No Inv : " . $orders->no_invoice . "\n");
        $printer->text("Customer : " . ($orders->customer_name ?? '-') . "\n");
        $printer->text("Kasir : " . ($orders->cashier_name ?? '-') . "\n");
        $printer->text("Waktu : " . $orders->created_at . "\n\n");

        // Print table header
        $printer->initialize();
        $printer->text("--------------------------\n");
        $printer->text(self::buatBaris2Kolom("Menu", "Qty"));

        // Print each order item based on category
        foreach ($orders->orderProducts as $orderProduct) {
            if ($orderProduct->category == $category) {
                $printer->text(self::buatBaris2Kolom(
                    $orderProduct->name,
                    $orderProduct->qty
                ));
            }
        }

        $printer->text("--------------------------\n");

        // Print thank you message
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("\nTerima kasih\n");

        // Cut the paper
        $printer->feed(5);
        $printer->cut();
        $printer->close();
    }

    public static function buatBaris2Kolom($kolom1, $kolom2)
    {
        $lebar_kolom_1 = 24;
        $lebar_kolom_2 = 5;

        $kolom1 = wordwrap($kolom1, $lebar_kolom_1, "\n", true);
        $kolom2 = wordwrap($kolom2, $lebar_kolom_2, "\n", true);

        $kolom1Array = explode("\n", $kolom1);
        $kolom2Array = explode("\n", $kolom2);

        $jmlBarisTerbanyak = max(count($kolom1Array), count($kolom2Array));

        $hasilBaris = array();

        for ($i = 0; $i < $jmlBarisTerbanyak; $i++) {
            $hasilKolom1 = str_pad((isset($kolom1Array[$i]) ? $kolom1Array[$i] : ""), $lebar_kolom_1, " ");
            $hasilKolom2 = str_pad((isset($kolom2Array[$i]) ? $kolom2Array[$i] : ""), $lebar_kolom_2, " ");

            $hasilBaris[] = $hasilKolom1 . " " . $hasilKolom2;
        }

        return implode("\n", $hasilBaris) . "\n";
    }

    public function printStruk($id)
    {
        $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');

        $orders = Order::findOrFail($id);
        $data['other_setting'] = OtherSetting::get()->first();

        $data['orders'] = $orders;
        return PDF::loadview('admin.pos.print.pdf', $data)->stream('order-' . $orders->id . '.pdf');
    }

    public function printProduct($id)
    {
        $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');

        $orders = Order::findOrFail($id);
        $data['other_setting'] = OtherSetting::get()->first();

        $data['orders'] = $orders;
        return PDF::loadview('admin.pos.print.product', $data)->stream('order-' . $orders->id . '.pdf');
    }

    public function strukDone($id)
    {
        $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');

        $orders = Order::findOrFail($id);
        $data['other_setting'] = OtherSetting::get()->first();

        $data['orders'] = $orders;
        return PDF::loadview('admin.pos.print.struk-done', $data)->stream('order-' . $orders->id . '.pdf');
    }

    public function printBill($id)
    {
        $data['current_time'] = Carbon::now()->format('Y-m-d H:i:s');

        $orders = Order::findOrFail($id);
        $data['other_setting'] = OtherSetting::get()->first();

        $data['orders'] = $orders;
        return PDF::loadview('admin.pos.print.print-bill', $data)->stream('order-' . $orders->id . '.pdf');
    }

    public function orderPesanan(Request $request)
    {
        $data['page_title'] = 'Order Pesanan';
        $data['account_users'] = User::get();

        $data['order_products'] = OrderProduct::orderBy('updated_at', 'ASC')->get();
        $data['other_setting'] = OtherSetting::get()->first();

        $type = $request->input('type', 'day');
        $cashierName = $request->user_id;
        $date = $request->input('start_date', date('Y-m-d'));

        if ($type == 'day') {
            if ($cashierName == 'All') {
                $orders = Order::whereDate('created_at', $date)
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $orders = Order::whereDate('created_at', $date)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        } elseif ($type == 'monthly') {
            $month = $request->input('month', date('m'));
            $monthPart = date('m', strtotime($month)); // Ensures the input is in 'm' format
            $orders = Order::whereMonth('created_at', $monthPart)
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($type == 'yearly') {
            $year = $request->input('year', date('Y'));
            $orders = Order::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->get();
        }

        $data['orders'] = $orders;

        foreach ($orders as $order) {
            $order->elapsed_time = $this->calculateElapsedTime($order->created_at);
        }

        return view('admin.pesanan.index', $data);
    }

    public function calculateElapsedTime($createdAt)
    {
        $now = Carbon::now();
        $created = Carbon::parse($createdAt);
        $elapsedTime = $created->diffForHumans($now);

        return $elapsedTime;
    }

    public function returnOrder(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->payment_status_midtrans = 'Paid';
            $order->status_product = 'Sewa Paid';

            if ($request->status_denda == 'Ada') {
                $order->payment_method2 = $request->payment_method;
            }
            $order->cash = $request->cash ?? 0;

            $dendaBarangRusak = $request->denda_barang_rusak ?? 0;
            $dendaMalam = $request->denda ?? 0;

            // Hitung subtotal per malam (subtotal / jumlah malam sewa)
            $subtotalPerMalam = $order->subtotal / $order->sewa;
            
            // Update subtotal jika ada denda malam
            $newSubtotal = $order->subtotal;
            if ($dendaMalam > 0) {
                $newSubtotal = $order->subtotal + ($subtotalPerMalam * $dendaMalam);
                $order->denda_malam = $subtotalPerMalam * $dendaMalam;
                $order->subtotal = $newSubtotal;
            }

            // Hitung diskon berdasarkan subtotal baru
            if ($order->type_discount == 'percent') {
                $discount = ($newSubtotal * $order->percent_discount) / 100;
            } else {
                $discount = $order->price_discount;
            }

            // Total akhir = (subtotal baru - diskon) + denda barang rusak
            $finalTotal = ($newSubtotal - $discount) + $dendaBarangRusak;

            // Hitung kembalian jika metode pembayaran adalah 'Cash'
            if ($request->payment_method == 'Cash' && $request->cash != null) {
                $kembalian = $request->cash - $finalTotal;
            }
            $order->kembalian = $kembalian ?? 0;
            $order->total = $finalTotal;

            $order->denda_barang_rusak = $dendaBarangRusak;
            $order->sewa = $order->sewa + $dendaMalam;
            $order->denda = $dendaMalam;

            $order->save();

            $customer = Customer::where('phone', $order->customer_phone)->first();
            $customer->status = 'Selesai';
            $customer->save();
            // Find the associated OrderProduct records
            $order_products = OrderProduct::where('order_id', $id)->get();

            foreach ($order_products as $order_product) {
                $products = Product::where('name', $order_product->name)->get();

                foreach ($products as $product) {
                    $product->current_stock += $order_product->qty;
                    $product->save();
                }
            }

            if ($request->cash) {
                return redirect()->back()->with('success', 'Uang Yang Di kembalikan ' . $kembalian);
            } else {
                return redirect()->back()->with('success', 'Update Return');
            }
        } catch (\Throwable $th) {
            // dd($th);
            return response()->json(['failed' => true, 'message' => $th->getMessage()]);
        }
    }

    public function CancelOrder(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->payment_status = 'Unpaid';

            $order->save();

            // Find the associated OrderProduct records
            $order_products = OrderProduct::where('order_id', $id)->get();

            foreach ($order_products as $order_product) {
                $products = Product::where('name', $order_product->name)->get();

                foreach ($products as $product) {
                    $product->current_stock += $order_product->qty;
                    $product->save();
                }
            }

            return redirect()->back()->with('success', 'Cancel Berhasil');

        } catch (\Throwable $th) {
            // dd($th);
            return response()->json(['failed' => true, 'message' => $th->getMessage()]);
        }
    }

}
