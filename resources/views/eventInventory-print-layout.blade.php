<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Andika Sari Catering</title>
    <style>
        body {
            font-family: Arial, sans-serif !important;
            margin: 20px;
            font-size: 12px;
        }

        .section-title {
            font-weight: bold;
            margin: 20px 0 10px;
        }

        /* Header Section */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid black;
        }

        .header .logo-container,
        .header .title-container {
            display: table-cell;
            vertical-align: middle;
        }

        .header .logo-container img {
            width: 50px;
            height: auto;
        }

        .header .title-container {
            text-align: right;
        }

        .header .title-container h1,
        .header .title-container h2 {
            margin: 0;
        }

        .header .title-container h1 {
            font-size: 22px !important;
        }

        .header .title-container h2 {
            font-size: 18px !important;
            font-weight: normal;
        }

        /* General Table */
        .table,
        .menu-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table td,
        .table th,
        .menu-table td,
        .menu-table th {
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }

        .table td:first-child,
        .menu-table td:first-child {
            width: 20%;
        }

        .table td:nth-child(2),
        .menu-table td:nth-child(2),
        .table td:last-child,
        .menu-table td:last-child {
            width: 30%;
        }

        .menu-table th:first-child {
            text-align: left;
            padding-left: 10px;
        }

        .menu-table th {
            font-weight: bold;
            text-align: center;
        }

        /* Input Fields */
        .table input {
            width: 100%;
            padding: 8px;
        }

        /* Main Layout for Two Columns */
        .main-layout {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .main-layout td {
            width: 50%;
            /* Kolom kiri dan kanan sama besar */
            vertical-align: top;
            /* Tabel di dalam sejajar di atas */
            padding: 10px;
        }

        /* Inner Layout untuk Tabel */
        .inner-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-layout th,
        .inner-layout td {
            padding: 8px;
            text-align: center;
            vertical-align: middle;
        }

        .inner-layout th {
            font-weight: bold;
            text-align: center;
        }

        .box {
            width: 30px;
            /* Panjang kotak */
            height: 20px;
            /* Tinggi kotak */
            border: 1px solid black;
            /* Warna border */
            /* Membuat sudut melengkung */
            display: inline-block;
            margin-top: 10px;
            margin-left: 5px;
            margin-right: 5px;
        }

        .notes-area {
            width: 100%;
            max-width: 98%;
            height: fit-content;
            padding: 0px 10px 10px 20px;
            box-sizing: border-box;
            margin: 0 auto;
            overflow-wrap: break-word;
            border: none;
        }

        .separator {
            border-bottom: 2px solid black;
            margin: 10px 0;
        }
    </style>
</head>

<body>
<div class="header">
    <div class="logo-container">
        <img src="{{ public_path('icon/andika-sari-icon.svg') }}" alt="Logo">
    </div>
    <div class="title-container">
        <h1>ANDIKA SARI CATERING</h1>
        <h2>DATA BARANG</h2>
    </div>
</div>

<div class="content">
    <table class="table">
        <tr>
            <th>NO. ORDER</th>
            <td>: <?= $order['order_name'] ?? '-' ?></td>
            <th>NOMOR TELP</th>
            <td>: <?= $order['customer']['phone_number_1'] ?? '-' ?>
                  <?php if (!empty($order['customer']['phone_number_2'])): ?>
                / <?= $order['customer']['phone_number_2'] ?>
                  <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>NAMA</th>
            <td>: <?= $order['customer']['name'] ?? '-' ?>
            </td>
            <th>TANGGAL ACARA</th>
            <td>:{{ formatTanggalIndo($order['event_date']) }}</td>
        </tr>
        <tr>
            <th>TEMPAT ACARA</th>
            <td>
                : <?= $order['location'] ?? '-' ?>
            </td>
        </tr>
    </table>
    <div class="separator"></div>
    <table class="main-layout">
        <tr>
            <!-- Kolom Kiri -->
            <td class="left-column">
                <table class="inner-layout">
                    <thead>
                    <tr>
                        <th>BARANG</th>
                        <th>JUMLAH</th>
                        <th>MASUK/KELUAR</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($items->chunk(ceil($items->count() / 2))->first() as $item)
                        <tr>
                            <td>{{ $item['inventory']['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>
                                <div class="box"></div>
                                <div class="box"></div>
                            </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
            <!-- Kolom Kanan -->
            <td class="right-column">
                <table class="inner-layout">
                    <thead>
                    <tr>
                        <th>BARANG</th>
                        <th>JUMLAH</th>
                        <th>MASUK/KELUAR</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($items->chunk(ceil($items->count() / 2))->last() as $item)
                        <tr>
                            <td>{{ $item['inventory']['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>
                                <div class="box"></div>
                                <div class="box"></div>
                            </td>
                            <td>

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <div class="separator"></div>
    <div class="section-title" style="padding-left: 10px">CATATAN :</div>
    <div class="notes-area"></div>
</div>
</body>

</html>
