<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pemesanan Bulanan</title>
    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 5px;
        }

        .header h2 {
            font-size: 15px;
            margin: 0 0 5px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary td {
            width: 50%;
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
        }

        .summary strong {
            display: block;
            font-size: 17px;
            margin-top: 5px;
        }

        .order {
            border: 1px solid #aaa;
            padding: 10px;
            margin-bottom: 15px;
        }

        .order-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .details td {
            padding: 4px;
            vertical-align: top;
        }

        .details td:first-child {
            width: 22%;
            font-weight: bold;
        }

        .menu {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .menu th,
        .menu td {
            border: 1px solid #bbb;
            padding: 5px;
            text-align: left;
        }

        .menu th {
            background-color: #eee;
        }

        .menu .center {
            text-align: center;
        }

        .note {
            margin-top: 8px;
            overflow-wrap: break-word;
        }

        .price {
            margin-top: 8px;
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        .empty {
            text-align: center;
            padding: 20px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>ANDIKA SARI CATERING</h1>
        <h2>REKAP PEMESANAN BULANAN</h2>
        <div>
            {{ \Carbon\Carbon::createFromDate($year, $month, 1)
                ->locale('id')->translatedFormat('F Y') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                TOTAL PESANAN
                <strong>{{ count($orders) }}</strong>
            </td>
            <td>
                TOTAL PORSI
                <strong>{{ number_format($totalPortion, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>

    @forelse ($orders as $item)
        @php
            $order = $item['order'];
            $orderPackage = $item['orderPackage'];
            $packageMenu = $item['packageMenu'];
            $customMenu = $item['customMenu'];

            $customer = data_get($order, 'customer');
            $detail = data_get($order, 'detail');
            $payment = data_get($order, 'payment');
        @endphp

        <div class="order">
            <div class="order-title">
                {{ $loop->iteration }}. {{ data_get($order, 'order_name', '-') }}
                — {{ data_get($customer, 'name', '-') }}
            </div>

            <table class="details">
                <tr>
                    <td>Tanggal acara</td>
                    <td>
                        {{ data_get($order, 'event_date')
                            ? \Carbon\Carbon::parse(data_get($order, 'event_date'))
                                ->locale('id')->translatedFormat('d F Y')
                            : '-' }}
                    </td>
                    <td>Jumlah porsi</td>
                    <td>{{ number_format((float) data_get($order, 'portion', 0), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>{{ data_get($order, 'event_time', '-') }}</td>
                    <td>Status</td>
                    <td>{{ data_get($order, 'status', '-') }}</td>
                </tr>
                <tr>
                    <td>Telepon</td>
                    <td>{{ data_get($customer, 'phone_number_1', '-') }}</td>
                    <td>Lokasi acara</td>
                    <td>{{ data_get($order, 'location', '-') }}</td>
                </tr>
                <tr>
                    <td>Alamat pelanggan</td>
                    <td colspan="3">{{ data_get($customer, 'address', '-') }}</td>
                </tr>
            </table>

            @if (count($orderPackage))
                <strong>Paket pesanan</strong>
                <table class="menu">
                    <thead>
                        <tr>
                            <th>Nama paket</th>
                            <th>Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderPackage as $package)
                            <tr>
                                <td>{{ data_get($package, 'package.name', '-') }}</td>
                                <td>{{ data_get($package, 'details', '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <strong>Menu pesanan</strong>
            <table class="menu">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Nama menu</th>
                        <th class="center">Porsi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packageMenu as $menu)
                        <tr>
                            <td>Paket</td>
                            <td>{{ data_get($menu, 'menu.name', '-') }}</td>
                            <td class="center">{{ data_get($menu, 'portion', '-') }}</td>
                            <td>{{ data_get($menu, 'details', '-') }}</td>
                        </tr>
                    @empty
                    @endforelse

                    @forelse ($customMenu as $menu)
                        <tr>
                            <td>Non-paket</td>
                            <td>{{ data_get($menu, 'menu.name', '-') }}</td>
                            <td class="center">{{ data_get($menu, 'portion', '-') }}</td>
                            <td>{{ data_get($menu, 'details', '-') }}</td>
                        </tr>
                    @empty
                    @endforelse

                    @if (count($packageMenu) === 0 && count($customMenu) === 0)
                        <tr>
                            <td colspan="4" class="center">Belum ada menu</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="note">
                <strong>Catatan:</strong>
                {{ data_get($order, 'note') ?: '-' }}
            </div>

            @if ($type !== 'dapur')
                <div class="price">
                    Total harga: Rp
                    {{ number_format((float) data_get($payment, 'amount', 0), 0, ',', '.') }}
                </div>
            @endif
        </div>
    @empty
        <div class="empty">
            Tidak ada pesanan untuk periode ini.
        </div>
    @endforelse

    <div class="footer">
        Andika Sari Catering — Rekap pemesanan bulanan
    </div>

</body>
</html>
