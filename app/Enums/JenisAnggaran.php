<?php

namespace App\Enums;

enum JenisAnggaran: string
{
    case Pendapatan = 'pendapatan';
    case Belanja = 'belanja';
    case Pembiayaan = 'pembiayaan';
}
