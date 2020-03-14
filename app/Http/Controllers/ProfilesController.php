<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;


class ProfilesController extends Controller
{   
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index(User $user)
    {

        $follows = (auth()->user()) ? auth()->user()->following->contains($user->profile) : false;

        // dd($follows);
        return view('profiles.index', compact('user', 'follows'));
        
        // $this->authorize('view', $user->profile);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user->profile);
   
        return view('profiles.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user->profile);

        $dataProfile = $request->validate([
            'website' => ['sometimes', 'url', 'nullable'],
            'bio' => ['sometimes', 'string', 'nullable'],
            'image' => ['sometimes', 'image', 'max:3000']
        ]);   

        $dataUser = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);   

        if(request('image')){
            $imagePath = request('image')->store('/profile', 'public');
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(300, 300);
            $image->save();
            $imageArray = ['image' => $imagePath ];
        }    

        auth()->user()->profile->update(array_merge(
            $dataProfile,
            $imageArray ?? []
            // ['image' => $imagePath ?? $user->profile->image]
        ));
        
        auth()->user()->update($dataUser);

        return redirect('/profile/' . auth()->user()->username);
    }
}
