<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return responder()->success(Product::with('user')->paginate(10))->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();        

        if ($data) {
            if (request()->has('picture'))
                $data['picture'] = $this->savePicture(request()->picture, auth()->user()->id);

            if (auth()->user()->role == 'customer')
                return responder()->error('Not Authorized', 'You are not authorized to perform this action.')->respond(403);
            
            $product = Product::create($data);
            return responder()->success($product)->respond();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return responder()->success($product)->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($data) {
            if (request()->has('picture'))

                if(isset($product->picture))
                    $this->deletePicture($product->picture);

                $data['picture'] = $this->savePicture(request()->picture, auth()->user()->id);

            if (auth()->user()->role == 'customer')
                return responder()->error('Not Authorized', 'You are not authorized to perform this action.')->respond(403);
            
            $product->update($data);
            return responder()->success($product)->respond();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if ((int) $product->user_id === (int) auth()->user()->id) {
            
            $this->deletePicture($product->picture);
            $product->delete();
            
            return responder()->success(['message' => 'deleted successfully!'])->respond(200);
        } else {            
            return responder()->error('Not Authorized', 'You are not Authorized to Delete this user')->respond(403);
        }
    }

    private function savePicture($base64, $entity)
    {
        $picture = explode(',', $base64);
        $picture = base64_decode($picture[1]); //base64 -> image        

        $name_picture = str_random(60) . '.jpeg';
        $path_save = 'storage/picture/' . $entity . '/' . $name_picture;
        $path_public = 'public/picture/' . $entity . '/' . $name_picture;

        $picture_save = Storage::put($path_public, $picture);

        return env('APP_URL') . '/' . $path_save;
    }

    private function deletePicture($picture)
    {
        $picture = explode('storage/', $picture);

        if (isset($picture[1])) {
            $picture = 'public/' . $picture[1];
            Storage::delete($picture);
        }
    }
}
