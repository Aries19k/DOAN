<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductType;
use App\Models\SlideShow;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;


use App\Models\Cart;
use App\Http\Services\Product\ProductService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MainController extends Controller
{
    protected $product;

    public function __construct(ProductService $product)
    {
        $this->product = $product;
    }

    public function index()
    {

        if (Auth::check()) {
            $userId = Auth::user()->id;

            $cartProducts = DB::table('carts')
                ->join('products', 'carts.product_id', '=', 'products.id')
                ->select(
                    'products.image',
                    'products.name',
                    'products.price',
                    'carts.quantity',
                    'products.id',
                )
                ->where('carts.user_id', $userId)
                ->get();

            Session::put('cart', $cartProducts);
            // dd(Session::get('cart'));
            return view('home', [
                'title' => 'Coza', 'cartProducts' => $cartProducts,
                'producttypes' => ProductType::select('id', 'name')->where('status', 1)->orderbyDesc('id')->get(),
                'slideshows' => SlideShow::where('status', 1)->get(),
                'products' => $this->product->get()
            ]);
        } else {
            return view('home', [
                'title' => 'Coza',
                'producttypes' => ProductType::select('id', 'name')->where('status', 1)->get(),
                'slideshows' => SlideShow::where('status', 1)->get(),
                'products' => $this->product->get()
            ]);
        }
    }
    const LIMIT = 16;



    public function loadProduct(Request $request)
    {
        $page = $request->input('page', 0);

        $result = $this->product->get($page);

        if (count($result) != 0) {
            $html = view('products.list',
            ['products' => $result])->render();

            return response()->json(['html' => $html]);
        }

        return response()->json(['html' => '']);
    }

    public function showCart()
    {
        $cartProducts = "";
        return view('header', ['cartProducts' => $cartProducts]);
    }

    public function searchprod(Request $request)
    {
        $searchProducts= Product::where('name','LIKE','%'.$request->search.'%')->latest()->paginate(15);
        // dd($searchProducts);
        return view('products.searchprodcuts', compact('searchProducts'),['title'=>'checkout']);
    }
}
