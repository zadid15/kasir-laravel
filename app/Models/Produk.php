<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $fillable = ['NamaProduk', 'Harga', 'Stok', 'Users_id'];
}
