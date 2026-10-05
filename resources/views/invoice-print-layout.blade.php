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
            padding-top: 4px;
            padding-bottom: 4px;
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

        .signature-table .signature-line {
            margin-top: 70px;
            margin-left: 10px;
        }


        .separator {
            border-bottom: 2px solid black;
            margin: 10px 0;
        }

        .tanggal {
            text-align: right;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
        }

        .invoice-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .invoice-table .text-right {
            text-align: right;
            padding-right: 12px;
        }

        .invoice-table .font-bold {
            font-weight: bold;
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
        <h2>INVOICE</h2>
    </div>
</div>
<div class="content">
    <h3 class="tanggal">SERANG, {{ formatTanggalIndo(now()) }}</h3>
    <table class="table">
        <tr>
            <th>NO. ORDER</th>
            <td>: <?= $resultPayment['order']['order_name'] ?? '-' ?></td>
            <th>NAMA</th>
            <td>: <?= $resultPayment['order']['customer']['name'] ?? '-' ?></td>
        </tr>
        <tr>
            <th>TEMPAT ACARA</th>
            <td>: <?= $resultPayment['order']['location'] ?></td>
            <th>TANGGAL ACARA</th>
            <td>: {{ formatTanggalIndo($resultPayment['order']['event_date']) }}</td>
        </tr>
    </table>
    <div class="separator"></div>
    <!-- Invoice Section -->
    <table class="invoice-table">
        <thead>
        <tr>
            <th>NO</th>
            <th>DESKRIPSI</th>
            <th>JUMLAH</th>
            <th>HARGA / PORSI</th>
            <th>TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($resultMenu??[] as $i => $item)
            @php
                $lineTotal = (int)$item['portion'] * (int)$item['price'];
                $grandTotal += $lineTotal;
            @endphp
            <tr>
                <td>{{$i+1}}</td>
                <td>{{$item['name']}}</td>
                <td>{{$item['portion']}}</td>
                <td>{{ number_format($item['price'],0,',','.') }}</td>
                <td>{{ number_format($lineTotal,0,',','.') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" class="text-right">Total</td>
            <td class="font-bold">{{ number_format($grandTotal,0,',','.') }}</td>
        </tr>
        </tfoot>
    </table>
    <div class="separator"></div>
    <table class="signature-table">
        <tr>
            <!-- Kolom Kanan: TANGGAL dan PIHAK KATERING -->
            <td class="signature-cell">
                <p>ANDIKA SARI CATERING</p>
                <div class="signature-line">(..................................)</div>
            </td>
        </tr>
    </table>

</div>
</body>

</html>
