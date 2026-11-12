<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Padanan tb_tag_galeri Raka (Motif Batik, Proses Pembuatan,
// Detail Tekstur, Busana Modern, Sentra Paoman).
class TagGaleri extends Model
{
    protected $table = 'tag_galeri';
    protected $fillable = ['label'];
}
