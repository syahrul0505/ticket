<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Coupons;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderCoupon;
use App\Models\OrderProduct;
use App\Models\OrderProductAddon;
use App\Models\OtherSetting;
use App\Models\Product;
use App\Models\QueueWhatsapp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PDF;


class OrderController extends Controller
{
    public function checkout(Request $request, $token)
    {
        DB::beginTransaction();
        try {
            $session_cart = Cart::session(Auth::user()->id)->getContent();
            $other_setting = OtherSetting::get()->first();
            $checkToken = Order::where('token', $token)->get();
            // $checkToken     = Order::where('token',$token)->where('payment_status', 'Paid')->get();
            $service = $other_setting->layanan / 100;
            $pb01 = $other_setting->pb01 / 100;
            $cash = (int) str_replace('.', '', $request['cash']);
            $total_price = 0;
            $customer_phone = $request->no_wa;
            if ($customer_phone && str_starts_with($customer_phone, '0')) {
                // Ganti '0' (index pertama) dengan '62'
                $customer_phone = '62' . substr($customer_phone, 1);
            }
            $customer = Customer::where('phone', $customer_phone)->first();
            $biaya_layanan = 0;
            $kembalian = 0;
            $sewa = $request->sewa_price ?? 1;

            // dd($request->all());
            if (count($checkToken) != 0) {
                return redirect()->back()->with(['failed' => 'Tidak dapat mengulang transaksi!']);
            }

            if ($other_setting->layanan != 0) {
                $biaya_layanan = Cart::getTotal() * $service;
                $total_price = Cart::getTotal() + $biaya_layanan;
            } else {
                $total_price = (Cart::getTotal() ?? '0');
                // $total_price    = $request->sub_total;
            }

            if ($other_setting->pb01 != 0) {
                $biaya_pb01 = $total_price * ($other_setting->pb01 / 100);
                $pb01 = $biaya_pb01;
                $total_price = $total_price + $biaya_pb01 * $sewa;
            } else {
                $total_price = ($total_price ?? '0') * $sewa;
            }

            // ===================By Discount====================
            $getDiscountPrice = ($request->discount_price ? (int) str_replace('.', '', $request->discount_price) : 0);
            $getDiscountPercent = ($request->discount_percent ? (int) $request->discount_percent : 0);


            if ($request->type_discount == 'percent') {
                $discount_amount = $total_price * $getDiscountPercent / 100;
            } else {
                $discount_amount = $getDiscountPrice;
            }



            $cartTotal = Cart::getTotal();
            $subtotal = $total_price;
            $service_by_discount = ($subtotal - $discount_amount) * ($other_setting->layanan / 100);
            $tax_by_discount = (($subtotal - $discount_amount) + $service_by_discount) * $other_setting->pb01 / 100;
            $total_price_by_discount = ($subtotal - $discount_amount) + $service_by_discount + $tax_by_discount;
            // ===================By Discount====================

            // Kembalian
            if ($request->payment_method == 'Cash' && $request->cash != null) {
                $kembalian = $cash - ($request->type_discount ? $total_price_by_discount : $total_price);
            }


            // =================Create Data Order================
            if ($request->button == 'simpan-bill') {
                if ($customer_phone == null) {
                    return redirect()->back()->with(['failed' => 'Harap Mengisi No WhatApp']);
                }

                if ($request->start_date == null && $request->end_date == null) {
                    return redirect()->back()->with(['failed' => 'Harap Mengisi Tanggal Penyewaan!']);
                }

                // dd($request->all());
                //  ORDER TAMABAHAN
                if ($request->status_order == 'Order Tambahan') {
                    $order = Order::where('no_invoice', $request->no_invoice)->whereDate('created_at', Carbon::today())->first();

                    // Ambil subtotal keranjang
                    $new_items_base_subtotal = $cartTotal;

                    // Ambil durasi sewa (malam) untuk item baru
                    $new_items_sewa_multiplier = $sewa;

                    // Subtotal akhir untuk item baru (x durasi sewa)
                    $new_items_final_subtotal = $new_items_base_subtotal * $new_items_sewa_multiplier;

                    // Hitung jumlah diskon untuk item baru
                    $discount_amount_for_new_items = 0;
                    if ($request->type_discount == 'percent') {
                        $discount_amount_for_new_items = $new_items_final_subtotal * ($getDiscountPercent / 100);
                    } else {
                        $discount_amount_for_new_items = $getDiscountPrice;
                    }

                    // Pastikan diskon tidak lebih besar dari total item baru
                    $discount_amount_for_new_items = min($discount_amount_for_new_items, $new_items_final_subtotal);

                    // Nilai item baru setelah didiskon 
                    $new_items_value_after_discount = $new_items_final_subtotal - $discount_amount_for_new_items;

                    // Hitung service item baru
                    $service_for_new_items = 0;
                    if ($other_setting->layanan > 0) {
                        $service_for_new_items = $new_items_value_after_discount * ($other_setting->layanan / 100);
                    }

                    // Hitung pajak hanya untuk item baru
                    $tax_for_new_items = 0;
                    if ($other_setting->pb01 > 0) {
                        $base_for_tax = $new_items_value_after_discount + $service_for_new_items;
                        $tax_for_new_items = $base_for_tax * ($other_setting->pb01 / 100);
                    }

                    // nilai akhir (grand total) order tambahan
                    $final_value_of_addition = $new_items_value_after_discount + $service_for_new_items + $tax_for_new_items;

                    $order->total_qty += array_sum($request->qty);

                    // Akumulasi nilai keuangan
                    $order->subtotal += $new_items_final_subtotal;
                    $order->price_discount += $discount_amount_for_new_items;
                    $order->service += $service_for_new_items;
                    $order->pb01 += $tax_for_new_items;
                    $order->total += $final_value_of_addition;

                    // sewa tambahan durasinya agar tercatat
                    $order->sewa += ($new_items_sewa_multiplier > 1 ? $new_items_sewa_multiplier : 0);

                    // Simpan semua perubahan
                    $order->save();

                } else {
                    $order = Order::create([
                        'no_invoice' => $this->generateInvoice(),
                        'cashier_name' => Auth::user()->fullname,
                        'customer_name' => $request->name_whatsapp ?? null,
                        'customer_email' => $customer->email ?? null,
                        'customer_phone' => $customer_phone ?? null,
                        'guarantee' => $request->guarantee ?? null,
                        'instagram' => $request->instagram ?? null,
                        'address' => $request->address ?? null,
                        'payment_status' => 'Paid',
                        'status_product' => 'Sewa',
                        'payment_method' => $request->payment_method,
                        'sewa' => $sewa,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'address_shop' => $other_setting->address,
                        'second_address_shop' => $other_setting->second_address,
                        'total_qty' => array_sum($request->qty),
                        'subtotal' => $cartTotal * $sewa,
                        'type_discount' => ($request->type_discount ? $request->type_discount : null),
                        //order baru, price_discount = diskon yang baru dihitung
                        'price_discount' => $discount_amount,
                        'percent_discount' => $getDiscountPercent,
                        'service' => ($request->type_discount ? $service_by_discount : $biaya_layanan),
                        'pb01' => ($request->type_discount ? $tax_by_discount : $pb01),
                        'total' => ($request->type_discount ? $total_price_by_discount : $total_price),
                        'cash' => $cash,
                        'kembalian' => $kembalian,
                        'token' => $token,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                // =================Create Data Customer ================
                if ($customer_phone) {
                    $code = Customer::latest()->first();

                    if ($code) {
                        $code = $code->code;
                        $code = substr($code, 4);
                        $code = intval($code) + 1;
                        $code = 'CUST' . str_pad($code, 5, '0', STR_PAD_LEFT);
                    } else {
                        $code = 'CUST00001';
                    }

                    // Update Customer
                    if ($customer) {
                        $customer->name = $request->name_whatsapp;
                        $customer->status = 'Sewa';
                        $customer->total_transaction += $order->total;
                        $customer->save();
                    } else {
                        // Jika belum ada, buat customer baru
                        Customer::create([
                            'code' => $code,
                            'name' => $request->name_whatsapp,
                            'phone' => $customer_phone,
                            'address' => $request->address,
                            'instagram' => $order->instagram,
                            'status' => 'Sewa',
                            'total_transaction' => $order->total
                        ]);
                    }
                }

                // =================Create Data Customer ================
            } else {
                $order = Order::create([
                    'no_invoice' => $this->generateInvoice(),
                    'cashier_name' => Auth::user()->fullname,
                    'customer_name' => $request->name_whatsapp_product ?? null,
                    'customer_email' => $customer->email ?? null,
                    'customer_phone' => $customer_phone,
                    'payment_status' => 'Paid',
                    'status_product' => 'Product',
                    'payment_method' => $request->payment_method ?? '-',
                    'sewa' => 0,

                    'address_shop' => $other_setting->address,
                    'second_address_shop' => $other_setting->second_address,

                    'total_qty' => array_sum($request->qty),
                    'subtotal' => $subtotal * $sewa,
                    'type_discount' => ($request->type_discount ? $request->type_discount : null),
                    'price_discount' => $getDiscountPrice,
                    'percent_discount' => $getDiscountPercent,
                    'service' => ($request->type_discount ? $service_by_discount : $biaya_layanan),
                    'pb01' => ($request->type_discount ? $tax_by_discount : $pb01),
                    'total' => ($request->type_discount ? $total_price_by_discount : $total_price),
                    'cash' => $cash,
                    'kembalian' => $kembalian,
                    'token' => $token,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // =================Create Data Customer ================
                if ($request->name_whatsapp) {
                    $code = Customer::latest()->first();

                    if ($code) {
                        $code = $code->code;
                        $code = substr($code, 4);
                        $code = intval($code) + 1;
                        $code = 'CUST' . str_pad($code, 5, '0', STR_PAD_LEFT);
                    } else {
                        $code = 'CUST00001';
                    }

                    if ($customer) {
                        // Jika nomor telepon sudah ada, update data customer
                        $customer->name = $request->name_whatsapp;
                        $customer->total_transaction += $order->total;
                        $customer->save();
                    } else {
                        // Jika belum ada, buat customer baru
                        Customer::create([
                            'code' => $code,
                            'name' => $request->name_whatsapp,
                            'phone' => $customer_phone,
                            'address' => $request->address,
                            'instagram' => $request->instagram,
                            'total_transaction' => $order->total
                        ]);
                    }
                }

                // =================Create Data Customer ================
            }


            // =================Create Data Order================

            // =================Order Coupon=====================
            // Check jika ada coupon yang dipilih
            if ($request->coupon_id) {
                $coupon = Coupons::findOrFail($request->coupon_id);
                $coupon_type = $coupon->type;
                $coupon_amount = 1;
                $temp_total = 0;
                $orderCoupon = OrderCoupon::create([
                    'order_id' => $order->id,
                    'name' => $coupon->name,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'discount_value' => $coupon->discount_value,
                    'discount_threshold' => (($coupon->type == 'Percentage Discount') ? $coupon->discount_threshold : null),
                    'max_discount_value' => (($coupon->type == 'Percentage Discount') ? $coupon->max_discount_value : null),
                ]);

                $coupon->current_usage += 1;
                $coupon->save();

                // Calculate discount amount based on coupon type
                if ($coupon_type == 'Percentage Discount') {
                    $coupon_amount = $subtotal * $coupon->discount_value / 100;

                    // Apply max discount value if applicable
                    if ($subtotal >= $coupon->discount_threshold && $coupon_amount > $coupon->max_discount_value) {
                        $coupon_amount = $coupon->max_discount_value;
                    }
                    $order->percent_discount = (int) $coupon->discount_value;
                    $order->price_discount = $coupon_amount;
                } else {
                    $coupon_amount = (int) $coupon->discount_value;
                }

                // Check Layanan
                if ($other_setting->layanan != 0) {
                    $biaya_layanan = ($subtotal - $coupon_amount) * $service;
                    $temp_total = $biaya_layanan;
                } else {
                    $temp_total = (($subtotal - $coupon_amount) ?? 0);
                }

                // Update tax & total price
                $taxPriceByCoupon = $temp_total + $biaya_layanan * ($other_setting->pb01 / 100);
                $totalPriceByCoupon = $temp_total + $biaya_layanan + $taxPriceByCoupon;

                // Set Data
                $order->is_coupon = true;
                if ($other_setting->layanan != 0) {
                    $order->pb01 = $taxPriceByCoupon;
                    $order->total = $totalPriceByCoupon;
                    $order->service = $biaya_layanan;
                    # code...
                } else {
                    $order->pb01 = 0;
                    $order->total = $temp_total;
                    $order->service = 0;
                }
                $order->save();
            }
            // =================Order Coupon=====================

            // ==================================================================================================
            // Order Product
            $orderProducts = []; // Array untuk menyimpan detail produk yang telah dimasukkan ke dalam pesanan
            $stockCheck = []; // Array untuk menyimpan jumlah total produk berdasarkan ID produk

            foreach ($session_cart as $cart) {
                $productId = $cart->attributes['product']['id'];
                $addonIds = isset($cart->attributes['addons']) && is_array($cart->attributes['addons']) ? array_map(function ($addon) {
                    return $addon['id'];
                }, $cart->attributes['addons']) : [];

                // Buat kunci unik berdasarkan ID produk dan ID addons
                $uniqueKey = $productId . '-' . implode('-', $addonIds);

                if (!isset($orderProducts[$uniqueKey])) {
                    $orderProducts[$uniqueKey] = [
                        'id' => $productId,
                        'name' => $cart->attributes['product']['name'],
                        'cost_price' => $cart->attributes['product']['cost_price'],
                        'selling_price' => $cart->attributes['product']['selling_price'],
                        'is_discount' => $cart->attributes['product']['is_discount'],
                        'percent_discount' => $cart->attributes['product']['percent_discount'],
                        'price_discount' => $cart->attributes['product']['price_discount'],
                        'category' => $cart->attributes['product']['category'],
                        'qty' => (int) $cart->quantity ,
                        'addons' => $cart->attributes['addons'],
                    ];
                } else {
                    $orderProducts[$uniqueKey]['qty'] += (int) $cart->quantity;
                }

                // Perbarui total kuantitas produk untuk pengecekan stok
                if (!isset($stockCheck[$productId])) {
                    $stockCheck[$productId] = 0;
                }
                $stockCheck[$productId] += (int) $cart->quantity;
            }

            // Pengecekan stok sebelum menyimpan ke tabel order_products
            foreach ($stockCheck as $productId => $totalQty) {
                $product = Product::findOrFail($productId);
                if ((int) $product->current_stock < $totalQty) {
                    return redirect()->back()->with(['failed' => 'Stock product ' . $product->name . ' kurang - Stock tersisa ' . $product->current_stock]);
                }

                // Kurangi stok produk
                $product->current_stock = (int) $product->current_stock - (int) $totalQty;
                $product->save();
            }

            // Simpan produk dan addons ke tabel order_products
            foreach ($orderProducts as $product) {
                // Buat entri order_product
                $orderProduct = OrderProduct::create([
                    'order_id' => $order->id,
                    'name' => $product['name'],
                    'cost_price' => $product['cost_price'],
                    'selling_price' => $product['selling_price'],
                    'is_discount' => $product['is_discount'],
                    'percent_discount' => $product['percent_discount'],
                    'price_discount' => $product['price_discount'],
                    'category' => $product['category'],
                    'qty' => $product['qty'],
                ]);

                // Simpan addons terkait ke tabel order_product_addons
                foreach ($product['addons'] as $addon) {
                    if (!empty($product['addons'])) {
                        $getAddon = Addon::findOrFail($addon['id']);
                        OrderProductAddon::create([
                            'order_product_id' => $orderProduct->id,
                            'name' => $getAddon->name,
                            'price' => $getAddon->price,
                        ]);
                    }
                }
            }

            // Jika semua operasi berhasil, lakukan commit
            DB::commit();

            // Hapus sesi keranjang setelah berhasil menyimpan data pesanan
            Cart::session(Auth::user()->id)->clear();

            if ($request->cash) {
                return redirect()->route('order-pesanan')->with('success', 'Uang Yang Di kembalikan ' . $kembalian);
            } else {
                return redirect()->route('order-pesanan')->with('success', 'Order Telah berhasil');
            }
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('failed', $th->getMessage());
        }
    }

    public function sendWhatsapp(Request $request, $id)
    {

        $order = Order::find($id);
        $order->status_realtime = 'before';
        $order->save();

        $other_setting = OtherSetting::first();

        $pdf = PDF::loadView('admin.pos.print.pdf', ['orders' => $order, 'other_setting' => $other_setting,]); // Bungkus object ke dalam array

        // Simpan atau stream PDF
        $filePath = storage_path('app/public/order-' . $order->no_invoice . '.pdf');
        $pdf->save($filePath);

        if ($order->customer_phone) {
            $queueWhatsapp = new QueueWhatsapp();
            $queueWhatsapp->phone_number = $order->customer_phone;
            $queueWhatsapp->message = 'order-' . $order['no_invoice'] . '.pdf'; // Simpan hanya nama file
            $queueWhatsapp->order_id = $order->id;
            $queueWhatsapp->save();
        }
        return redirect()->back()->with('success', 'Struk Sudah Terkirim');
    }

    public function sendWhatsappDone(Request $request, $id)
    {

        $order = Order::find($id);
        $other_setting = OtherSetting::first();
        $order->status_realtime = 'done';
        $order->save();

        $pdf = PDF::loadView('admin.pos.print.pdf', ['orders' => $order, 'other_setting' => $other_setting,]); // Bungkus object ke dalam array

        // Simpan atau stream PDF
        $filePath = storage_path('app/public/order-' . $order->no_invoice . '.pdf');
        $pdf->save($filePath);

        if ($order->customer_phone) {
            $queueWhatsapp = new QueueWhatsapp();
            $queueWhatsapp->phone_number = $order->customer_phone;
            $queueWhatsapp->message = 'order-' . $order['no_invoice'] . '.pdf'; // Simpan hanya nama file
            $queueWhatsapp->order_id = $order->id;
            $queueWhatsapp->save();
        }
        return redirect()->back()->with('success', 'Struk Sudah Terkirim');
    }

    private function generateInvoice()
    {
        // Ambil tanggal hari ini
        $today = Carbon::today();
        $formattedDate = $today->format('ymd'); // Format tanggal: yyMMdd

        // Ambil order terakhir yang dibuat hari ini dan sudah dibayar
        $lastOrder = Order::whereDate('created_at', $today)
            //   ->where(' payment_status', 'Paid')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            // Cek apakah order dibuat pada tanggal yang sama dengan hari ini
            $lastInvoiceNumber = $lastOrder->no_invoice;
            // Ambil nomor order dari string no_invoice (sesuaikan dengan format substring jika diperlukan)
            $lastOrderNumber = (int) substr($lastInvoiceNumber, 7);
            $nextOrderNumber = $lastOrderNumber + 1;
        } else {
            $nextOrderNumber = 1;
        }

        // Tambahkan padding agar nomor order menjadi 3 digit
        $paddedOrderNumber = str_pad($nextOrderNumber, 3, '0', STR_PAD_LEFT);
        // Buat nomor invoice
        $invoiceNumber = $formattedDate . '-' . $paddedOrderNumber;

        return $invoiceNumber;
    }
}
