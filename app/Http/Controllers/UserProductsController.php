<?php

namespace App\Http\Controllers;

use App\User;
use App\Product;
use App\Transformers\UserProductsTransformer;

class UserProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $products = Product::where('user_id', $user->id)->paginate(10);
        return responder()->success($products, UserProductsTransformer::class)->respond();
    }    
}
