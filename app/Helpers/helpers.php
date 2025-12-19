<?php

if (!function_exists('terbilang')) {
    function terbilang($angka)
    {
        $angka = abs($angka);
        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");

        if ($angka < 12) {
            return $baca[$angka];
        } else if ($angka < 20) {
            return terbilang($angka - 10) . " belas";
        } else if ($angka < 100) {
            return terbilang($angka / 10) . " puluh " . terbilang($angka % 10);
        } else if ($angka < 200) {
            return "seratus " . terbilang($angka - 100);
        } else if ($angka < 1000) {
            return terbilang($angka / 100) . " ratus " . terbilang($angka % 100);
        } else if ($angka < 2000) {
            return "seribu " . terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            return terbilang($angka / 1000) . " ribu " . terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            return terbilang($angka / 1000000) . " juta " . terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            return terbilang($angka / 1000000000) . " milyar " . terbilang($angka % 1000000000);
        } else if ($angka < 1000000000000000) {
            return terbilang($angka / 1000000000000) . " trilyun " . terbilang($angka % 1000000000000);
        }
    }
}