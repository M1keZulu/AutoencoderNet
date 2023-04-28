<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $images = Image::all();
        $user = Auth::user();

        return view('home', compact('images', 'user'));
    }

    public function show(Request $request)
    {
        $image = Image::find($request->id);
        $image->views++;
        $image->update(['views' => $image->views]);

        $comments = $image->comments()->with('user')->get();
        $user = Auth::user();

        return view('show', compact('image', 'comments'));
    }

}
