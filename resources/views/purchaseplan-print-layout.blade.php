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
            border: 1px solid black;
            border-radius: 15px;
            box-sizing: border-box;
        }

        /* Notes Area */
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

        /* Signature Section */
        .signature-table td {
            text-align: left;
            vertical-align: top;
            width: 50%;
            padding: 0 10px;
        }

        .signature-line {
            margin-top: 40px;
            font-style: italic;
            text-align: left;
            display: inline-block;
            width: 100%;
        }

        .signature-cell p {
            font-weight: bold;
        }

        .signature-table .left-cell .signature-line {
            margin-top: 97px;
        }

        .signature-table .right-cell .signature-line {
            margin-top: 70px;
        }

        .separator {
            border-bottom: 2px solid black;
            margin: 10px 0;
        }

        .purchase-table {
            width: 100%;
        }

        .purchase-table th,
        .purchase-table td {
            width: 25%;
            text-align: center;
            vertical-align: middle;
            padding: 8px;
        }

        .purchase-table th {
            font-weight: bold;
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
            <h2>PEMBELANJAAN</h2>
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
        </table>
        <div class="separator"></div>
        <table class="purchase-table">
            <tr>
                <th>BAHAN</th>
                <th>JUMLAH</th>
                <th>SATUAN</th>
                <th>STATUS</th>
            </tr>
            @foreach ($totalIngridients as $ingridient)
                <tr>
                    <td><?= $ingridient['name'] ?></td>
                    <td><?= $ingridient['quantity'] ?></td>
                    <td><?= $ingridient['unit'] ?></th>
                    <td>
                        <div class="box"></div>
                    </td>
                </tr>
            @endforeach
        </table>
        <div class="separator"></div>
        <div class="section-title" style="padding-left: 10px">CATATAN :</div>
        <div class="notes-area"></div>
    </div>
</body>

</html>
