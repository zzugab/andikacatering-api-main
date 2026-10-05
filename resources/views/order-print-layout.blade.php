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
    </style>
</head>

<body>
<div class="header">
    <div class="logo-container">
        <img src="{{ public_path('icon/andika-sari-icon.svg') }}" alt="Logo">
    </div>
    <div class="title-container">
        <h1>ANDIKA SARI CATERING</h1>
        <h2>PEMESANAN</h2>
    </div>
</div>
<div class="content">
    <table class="table">
        <tr>
            <th>NO. ORDER</th>
            <td>: <?= $order['order_name'] ?? '-' ?></td>
            <th>JUMLAH PORSI</th>
            <td>: <?= $order['portion'] ?? 0 ?></td>
        </tr>
        <tr>
            <th>NAMA</th>
            <td>: <?= $order['customer']['name'] ?? '-' ?>
            </td>
            <th>PRASMANAN UMUM</th>
            <td>: <?= $order['detail']['general_buffet'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>NOMOR TELP</th>
            <td>: <?= $order['customer']['phone_number_1'] ?? '-' ?>
                  <?php if (empty($order['customer']['phone_number_2'])): ?>
                / <?= $order['customer']['phone_number_2'] ?>
                  <?php endif; ?>
            </td>
            <th>PRASMANAN VIP</th>
            <td>: <?= $order['detail']['vip_buffet'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>ALAMAT</th>
            <td>:
                <?= $order['customer']['address'] ?? '-' ?>
            </td>
            <th>MEJA VIP</th>
            <td>:
                <?= $order['detail']['vip_table'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>LOKASI ACARA</th>
            <td>:
                <?= $order['location'] ?? '-' ?>
            </td>
            <th>MEJA MAKAN PENGANTIN</th>
            <td>:
                <?= $order['detail']['wedding_food_table'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>TANGGAL ACARA</th>
            <td>:
                {{ formatTanggalIndo($order['event_date']) }}
            </td>
            <th>MEJA PENERIMA TAMU</th>
            <td>:
                <?= $order['detail']['reception_table'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>WAKTU</th>
            <td>:
                {{ formatJamMenit($order['event_time']) }}
            </td>
            <th>BUAT NAIB</th>
            <td>:
                <?= $order['detail']['for_naib'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <th>AKAD</th>
            <td>:
                {{ formatJamMenit($order['detail']['akad_start']) }}
            </td>
            <th>AYAM BEKAKAK + NASI PUNAR</th>
            <td>:
                <?= $order['detail']['ayam_bekakak_nasi_punar'] ?? 'tidak' ?>
            </td>
        </tr>
        <tr>
            <th>RESEPSI</th>
            <td>:
                {{ formatJamMenit($order['detail']['resepsi_start']) }}
            </td>
            <th>BAWA MIKA BUAT BESAN</th>
            <td>:
                <?= $order['detail']['mica_for_besan'] ?? 'tidak' ?>
            </td>
        </tr>
        <tr>
            @if($type!='dapur')
                <th>TOTAL HARGA</th>
                <td>: Rp {{ number_format($order['payment']['amount'] ?? 0, 0, ',', '.') }}</td>
            @endif
        </tr>
    </table>
    <div class="separator"></div>
    <table class="table">
        @if (!isset($orderPackage))
            <tr>
                <th>PAKET</th>
                <td>-</td>
                <th>DESKRIPSI</th>
                <td>-</td>
            </tr>
        @else
            @foreach ($orderPackage as $package)
                <tr>
                    @if ($loop->index == 0)
                        <th>PAKET</th>
                    @else
                        <td></td>
                    @endif
                    <td><?= $package['package']['name'] ?? '-' ?></td>
                    <th>DESKRIPSI</th>
                    <td><?= $package['details'] ?? '-' ?></td>
                </tr>
            @endforeach
        @endif
    </table>
    <div class="separator"></div>
    <!-- Menu Section -->
    <table class="menu-table">
        <thead>
        <tr>
            <th>MENU (PAKET)</th>
            <th>MENU</th>
            <th>PORSI</th>
            <th>KETERANGAN</th>
        </tr>
        </thead>
        <tbody>
        @if (!isset($packageMenu))
            <tr>
                <td></td>
                <td>-</td>
                <td style="text-align: center !important">-</td>
                <td style="text-align: center !important">-</td>
            </tr>
        @else
            @foreach ($packageMenu as $menu)
                <tr>
                    <td></td>
                    <td><?= $menu['menu']['name'] ?? '-' ?></td>
                    <td style="text-align: center !important"><?= $menu['portion'] ?? '-' ?></td>
                    <td style="text-align: center !important"><?= $menu['details'] ?? '-' ?></td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
    <table class="menu-table">
        <thead>
        <tr>
            <th>MENU (NON-PAKET)</th>
            <th>MENU</th>
            <th>PORSI</th>
            <th>KETERANGAN</th>
        </tr>
        </thead>
        <tbody>
        @if (!isset($customMenu))
            <tr>
                <td></td>
                <td>-</td>
                <td style="text-align: center !important">-</td>
                <td style="text-align: center !important">-</td>
            </tr>
        @else
            @foreach ($customMenu as $menu)
                <tr>
                    <td></td>
                    <td><?= $menu['menu']['name'] ?? '-' ?></td>
                    <td style="text-align: center !important"><?= $menu['portion'] ?? '-' ?></td>
                    <td style="text-align: center !important"><?= $menu['details'] ?? '-' ?></td>
                </tr>
            @endforeach
        @endif

        </tbody>
    </table>
    <div class="separator"></div>

    <div class="section-title" style="padding-left: 10px">CATATAN :</div>
    <div class="notes-area">
        <?= $order['note'] ?? '-' ?>
    </div>
    <div class="separator"></div>

    @if($type!='dapur')
        <table class="signature-table">
            <tr>
                <!-- Kolom Kiri: PENERIMA -->
                <td class="signature-cell left-cell">
                    <p>PENERIMA</p>
                    <div class="signature-line">(...........................)</div>
                </td>
                <!-- Kolom Kanan: TANGGAL dan PIHAK KATERING -->
                <td class="signature-cell right-cell">
                    <p>SERANG, {{ formatTanggalIndo(now()) }}</p>
                    <p>ANDIKA SARI CATERING</p>
                    <div class="signature-line">(...........................)</div>
                </td>
            </tr>
        </table>
    @endif

</div>
</body>

</html>
