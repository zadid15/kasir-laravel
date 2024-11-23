<?php

namespace App\Http\Controllers;

use App\Models\LogStok;
use App\Models\Produk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Milon\Barcode\Facades\DNS1DFacade;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = 'Product';
        $subtitle = 'Index';
        $produks = Produk::all();
        return view('admin.produk.index', compact('title', 'subtitle', 'produks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Product';
        $subtitle = 'Create';
        return view('admin.produk.create', compact('title', 'subtitle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'NamaProduk' => 'required',
            'Harga' => 'required|numeric',
            'Stok' => 'required|numeric',
        ]);
        $validate['Users_id'] = Auth::user()->id;
        $simpan = Produk::create($validate);
        if ($simpan) {
            return response()->json(['status' => 200, 'message' => 'Product has been successfully added!']);
        } else {
            return response()->json(['status' => 500, 'message' => 'Product failed to add!']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $title = 'Product';
        $subtitle = 'Edit';
        $produk = Produk::find($id);

        return view('admin.produk.edit', compact('title', 'subtitle', 'produk'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        $validate = $request->validate([
            'NamaProduk' => 'required',
            'Harga' => 'required|numeric',
            'Stok' => 'required|numeric',
        ]);
        $validate['Users_id'] = Auth::user()->id;
        $simpan = $produk->update($validate);
        if ($simpan) {
            return response()->json(['status' => 200, 'message' => 'Product has been successfully changed!']);
        } else {
            return response()->json(['status' => 500, 'message' => 'Product failed to change!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);
        $delete = $produk->delete();
        if ($delete) {
            return redirect(route('produk.index'))->with('success', 'Product has been successfully deleted!');
        } else {
            return redirect(route('produk.index'))->with('error', 'Product failed to delete!');
        }
    }

    public function tambahStok(Request $request, $id)
    {
        $validate = $request->validate([
            'Stok' => 'required|numeric',
        ]);
        $produk = Produk::find($id);
        $produk->Stok += $validate['Stok'];
        $update = $produk->save();
        if($update)
        {
            return response()->json(['status' => 200, 'message' => 'Stock has been successfully added!']);
        } else{
            return response()->json(['status' => 500, 'message' => 'Stock failed to added!']);
        }
    }

    public function logproduk()
    {
        $title = 'Product';
        $subtitle = 'Log Product';
        $produks = LogStok::join('produks', 'log_stoks.ProdukId', '=', 'produks.id')
        ->join('users', 'log_stoks.UsersId', '=', 'users.id')
        ->select('log_stoks.JumlahProduk', 'log_stoks.created_at', 'produks.NamaProduk', 'users.name')->get();
        return view('admin.produk.logproduk', compact('title', 'subtitle', 'produks'));
    }

    public function cetaklabel(Request $request)
    {
        $id_produk = $request->id_produk;
        $barcodes = [];

        if(is_array($id_produk)){
            foreach ($id_produk as $id) {
                $id = (string) $id;
                $harga = Produk::find($id)->Harga;
                $barcode = DNS1DFacade::getBarcodeHTML($id, 'C128');
                $barcodes[] = ['barcode' => $barcode, 'harga' => $harga];
            }
        } else {
            $id_produk = (string) $id_produk;
            $harga = Produk::find($id_produk)->Harga;
            $barcode = DNS1DFacade::getBarcodeHTML($id_produk, 'C128');
            $barcodes[] = ['barcode' => $barcode, 'harga' => $harga];
        }
        $pdf = Pdf::loadView('admin.produk.cetaklabel', compact('barcodes'));
        $file_path = storage_path('app/public/barcodes.pdf');
        $pdf->save($file_path);

        return response()->json(['url' => asset('storage/barcodes.pdf')]);
    }
}
