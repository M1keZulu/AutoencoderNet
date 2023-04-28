<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function store(Request $request)
    {   
        
        if(!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to access this page');
        }

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'currency' => 'required',
            'image_path' => 'required|image',
        ]);
        
        $image = new Image;
        $image->name = $request->name;
        $image->description = $request->description;
        $image->price = $request->price;
        $image->currency = $request->currency;
        $imagePath = $request->file('image_path')->getClientOriginalName();
        $image->image_path = $imagePath;
        $request->file('image_path')->storeAs('public/images', $imagePath);
        $image->save();
      
        return redirect()->route('home')->with('success', 'Image uploaded successfully');
    }
       
    public function update(Request $request, Image $image)
    {
        if(!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to access this page');
        }

        $request->validate([
            'id' => 'required|numeric'
        ]);

        $image = Image::find($request->id);
    
        if($request->name) {
            $image->name = $request->name;
        }
        if($request->description) {
            $image->description = $request->description;
        }
        if($request->price) {
            $image->price = $request->price;
        }
        if($request->currency) {
            $image->currency = $request->currency;
        }
        $image->update();

        return redirect()->route('comment.show', ['id' => $request->id])->with('success', 'Image updated successfully');
    }

    public function destroy(Request $request)
    {   
        if(!Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to access this page');
        }

        $request->validate([
            'id' => 'required|numeric',
        ]);

        $image = Image::find($request->id);        
        $StoragePath = 'public/images/' . $image->image_path;
        Storage::delete($StoragePath);
        $image->delete();

        return redirect()->route('home')->with('success', 'Image deleted successfully');
    }
}
