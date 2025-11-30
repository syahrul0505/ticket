<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="style.css">
    <title>Receipt | Management POS</title>
    <style>
        * {
            font-size: 14px;
            font-family: 'Times New Roman';
        }

        td, th, tr, table {
            border-top: 1px solid black;
            border-collapse: collapse;
            padding: 4px;
        }

        td.description, th.description {
            width: 30px;
            max-width: 30px;
        }

        td.quantity, th.quantity {
            width: 100px;
            max-width: 20px;
            word-break: break-all;
            text-align: center;
        }
        td.menu, th.menu {
            width: 100px;
            max-width: 90px;
            word-break: break-all;
            text-align: center;
        }

        td.price, th.price {
            width: 100px;
            max-width: 120px;
            word-break: break-all;
            text-align: center;
        }

        td.sub-total, th.sub-total {
            width: 10px;
            max-width: 120px;
        }

        td.pb01, th.pb01 {
            width: 10px;
            max-width: 20px;
            word-break: break-all;
            text-align: center;
        }

        td.total, th.total {
            width: 80px;
            max-width: 80px;
            word-break: break-all;
            text-align: center;
        }

        .centered {
            text-align: center;
            align-content: center;
            margin: 0;
            font-weight: 400;
        }

        .ticket {
            width: 100%;
            text-align: center;
        }

        img {
            width: 100px;
        }

        @media print {
            .hidden-print, .hidden-print * {
                display: none !important;
            }
        }

        @page {
            size: 70mm 270mm;
            margin: 5;
        }

        .head__text {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            padding: 0;
        }

        .line {
            width: 100%;
            border-top: 1px dashed #3f3f3f;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .invoiceNumber {
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding-left: 5px;
            padding-right: 5px;
            text-align: left;
            margin-top: 3px;
            margin-bottom: 3px;
        }

        .invoiceNumber > div {
            font-weight: 400;
        }
    </style>
</head>
<body>

    <div class="ticket">
        <img src="{{ asset('images/products/'.$other_setting->logo ?? 'https://ui-avatars.com/api/?name=No+Image') }}" alt="Logo">
        <h2 class="head__text">{{ $other_setting->name_brand }}
            <span class="centered">
                <br>
                {{ $other_setting->address }}
                <br>
            </span>
        </h2>
        <div class="line"></div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                No. Invoice:
                <span style="float: right; margin-right: 15px;">#{{ $orders->no_invoice }}</span>
            </div>
        </div>


        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Metode Pembayaran:
                <span style="float: right; margin-right: 15px;"> {{ $orders->payment_method }}</span>
            </div>
        </div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Datetime:
                <span style="float: right; margin-right: 15px;">{{ $orders->created_at }}</span>
            </div>
        </div>
        <div class="line"></div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Penyewa:
                <span style="float: right; margin-right: 15px;">{{ $orders->customer_name ?? '-' }} </span>
            </div>
        </div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Alamat:
                <span style="float: right; margin-right: 15px;">{{ $orders->address ?? '-' }} </span>
            </div>
        </div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                No Tlp:
                <span style="float: right; margin-right: 15px;">{{ $orders->customer_phone ?? '-' }} </span>
            </div>
        </div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Instagram:
                <span style="float: right; margin-right: 15px;">{{ $orders->instagram ?? '-' }} </span>
            </div>
        </div>

        <div class="invoiceNumber">
            <div style="margin-left: 2px;">
                Jaminan:
                <span style="float: right; margin-right: 15px;">{{ $orders->guarantee ?? '-' }} </span>
            </div>
        </div>

        <div style="margin-bottom: 5px"></div>
        <table>
            <thead>
                <tr>
                    <th class="menu">Menu</th>
                    <th class="description">Qty</th>
                    <th class="price">Price</th>
                </tr>
            </thead>
            <tbody>

            @php

            $totalPrice = 0;
            @endphp
                @foreach ($orders->orderProducts as $orderPivot)

                    <tr>
                        <td class="menu"> {{ $orderPivot->name }} </td>
                        <td class="description" style="text-align: center">{{ $orderPivot->qty }}</td>
                        <td class="price" style="text-align: right">Rp.{{ number_format($orderPivot->selling_price * $orderPivot->qty,0) }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td class="sub-total"> &nbsp;</td>
                    <td class="pb01">&nbsp;</td>
                    <td class="total" style="text-align: right">&nbsp;</td>
                </tr>

                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Sewa</td>
                    <td class="total" style="text-align: right">{{ number_format($orders->sewa,0) }} Malam</td>
                </tr>

                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Sub Total</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->subtotal,0) }}</td>
                </tr>

                @if ($orders->is_coupon == true)
                    <tr>
                        <td class="pb01" style="text-align: left" colspan="2">Coupon</td>
                        <td class="total" style="text-align: right">
                            @foreach ($orders->orderCoupons as $orderCoupon)
                                <div class="d-flex flex-row align-items-center">
                                    <span class="fs-6"> ({{ $orderCoupon->name ?? '-' }})</span>
                                </div>
                                <span>Rp.{{ number_format($orderCoupon->discount_value,0) }}</span>
                            @endforeach
                        </td>
                    </tr>
                    @elseif($orders->type_discount)
                    <tr>
                        <td class="pb01" style="text-align: left" colspan="2">Discount</td>
                        <td class="total" style="text-align: right">
                            <span>
                                @if($orders->type_discount == 'percent')
                                    {{ $orders->percent_discount }}%
                                @else
                                    Rp.{{ number_format($orders->price_discount,0) }}
                                @endif
                            </span>
                        </td>
                    </tr>
                @endif

                @if ($orders->denda != null)
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Denda Malam</td>
                    <td class="total" style="text-align: right">{{ number_format($orders->denda,0) }} Malam ({{ number_format($orders->denda_malam,0) }})</td>
                </tr>
                @endif

                @if ($orders->denda_barang_rusak != null)
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Denda Barang Rusak</td>
                    <td class="total" style="text-align: right">Rp. {{ number_format($orders->denda_barang_rusak,0) }}</td>
                </tr>
                @endif

                {{-- @if ($orders->type_discount == 'price')
                <tr style="margin-top: 20px !important;">
                    <td class="pb01"  style="text-align: left"colspan="2">Discount</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->discount_price,0) }}</td>
                </tr>
                @else
                <tr style="margin-top: 20px !important;">
                    <td class="pb01" style="text-align: left" colspan="2">Discount ({{ $orders->discount_percent }}%)</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->sub_total * $orders->discount_percent /100,0) }}</td>
                </tr>
                @endif --}}
                @if ($orders->payment_method == "Cash")
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Tunai</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->cash,0) }}</td>
                </tr>

                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Kembalian</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->kembalian,0) }}</td>
                </tr>
                @endif
                @if ($orders->pb01 != 0)
                <tr style="margin-top: 20px !important;">
                    <td class="pb01" style="text-align: left" colspan="2">Pajak</td>
                    <?php
                        $biaya_pb01 = $totalPrice * $orders->persentase_pb01/100;
                    ?>
                    <td class="total" style="text-align: right">Rp.{{ number_format($biaya_pb01,0) }}</td>
                </tr>

                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Kembalian</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->kembalian,0) }}</td>
                </tr>
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Total</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->total,0) }}</td>
                </tr>
                @else
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Total</td>
                    <td class="total" style="text-align: right">Rp.{{ number_format($orders->total)}}</td>
                </tr>
                @endif

                @if ($orders->start_date)
                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Tanggal Ambil</td>
                    <td class="total" style="text-align: right">{{ $orders->start_date }}</td>
                </tr>

                <tr>
                    <td class="pb01" style="text-align: left" colspan="2">Tanggal Kembali</td>
                    <td class="total" style="text-align: right">{{ $orders->end_date }}</td>
                </tr>
                    
                @endif
                
            </tbody>
        </table>

        <div class="line"></div>
        {{-- <p class="centered" style="margin-top: 10px; font-weight: 600;">Bill Terbayar </p> --}}
        <p class="centered" style="margin-top: 10px; font-weight: 600;">{{ $other_setting->name_footer }}</p>
        <div class="line"></div>
        <p>Terima kasih sudah menyewa perlengkapan camping di tempat kami! Semoga perjalanan Anda seru dan penuh pengalaman berkesan. Jangan ragu untuk kembali lagi ya!</p>
        <span class="centered">
            <br>
            {{ $orders->address_shop }}
            <br>
            <br>
            {{ $orders->second_address_shop }}
            <br>
        </span>
        </div>
</body>
</html>
