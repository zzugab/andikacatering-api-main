<?php

if (!function_exists('formatJamMenit')) {
    /**
     * Format waktu (jam dan menit saja).
     *
     * @param \Illuminate\Support\Carbon|string|null $tanggal
     * @return string
     */
    function formatJamMenit($tanggal = null)
    {
        // Jika parameter null atau kosong, kembalikan nilai default '-'
        if ($tanggal === null || $tanggal === '') {
            return '-';
        }

        try {
            // Parsing input menjadi Carbon
            $tanggal = \Carbon\Carbon::parse($tanggal);
        } catch (\Exception $e) {
            return '-'; // Jika parsing gagal, kembalikan nilai default '-'
        }

        // Format waktu: HH:MM
        return $tanggal->format('H:i');
    }
}
if (!function_exists('formatTanggalIndo')) {
    /**
     * Format tanggal ke dalam bahasa Indonesia.
     *
     * @param \Illuminate\Support\Carbon|null|string $tanggal
     * @return string
     */
    function formatTanggalIndo($tanggal = null)
    {
        // Jika tanggal kosong/null, kembalikan "-"
        if (is_null($tanggal) || $tanggal === '') {
            return '-';
        }

        // Gunakan Carbon untuk memastikan format tanggal valid
        try {
            $tanggal = \Carbon\Carbon::parse($tanggal);
        } catch (\Exception $e) {
            return '-'; // Jika parsing gagal, kembalikan "-"
        }

        // Nama-nama bulan dalam Bahasa Indonesia
        $bulanIndo = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        // Format tanggal: DAY MONTH YEAR (uppercase)
        return strtoupper($tanggal->day . ' ' . $bulanIndo[$tanggal->month - 1] . ' ' . $tanggal->year);
    }
}
