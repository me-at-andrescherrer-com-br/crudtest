<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role = (request()->role ? request()->role : 'customer');

        if ($role === 'producer')
            return responder()->success(User::where('role',$role)->with('products')->paginate(10))->respond();
        else if ($role === 'all')
            return responder()->success(User::paginate(10))->respond();

        return responder()->success(User::where('role',$role)->paginate(10))->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {                
        $validated = $request->validated();

        if ($validated) {
            $user = User::create([
                'name'  => $request->name,
                'email'  => $request->email,
                'password'  => Hash::make($request->password),
                'email_verified_at' => now(),
                'role'  => $request->role,
            ]);            

            return responder()->success($user)->respond(201);
        }        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        if ($user)
            return responder()->success($user)->respond();    
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if($validated) {
            foreach($request->all() as $k => $v) {
                $v = ($k === 'password' ? Hash::make($v) : $v); 
                $user->$k = $v;
            }
            $user->update();
            
            return responder()->success($user)->respond(200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if (auth()->user()->role === 'admin') {        

            foreach($user->products as $product) {
                $this->deletePicture($product->picture);
            }
            
            $user->delete();
    
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
