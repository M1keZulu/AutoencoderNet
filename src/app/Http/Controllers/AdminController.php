<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Embeddings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        
        try {
            $image = new Image;
            $image->name = $request->name;
            $image->description = $request->description;
            $image->price = $request->price;
            $image->currency = $request->currency;
            $imagePath = $request->file('image_path')->getClientOriginalName();
            $imagePath = time() . '_' . $imagePath;
            $image->image_path = $imagePath;
            $request->file('image_path')->storeAs('public/images', $imagePath);

            $url = \config('api.url') . '/success';
            Log::info($url);
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url, [
                'multipart' => [
                    [   
                        'name' => 'file',
                        'contents' => fopen(storage_path('app/public/images/' . $imagePath), 'r')
                    ]
                ]
            ]);
            $response = json_decode($response->getBody(), true);

            $image->save();
            
            $count = $response['count'];
            for($i = 0; $i < $count; $i++) {
                $embedding = new Embeddings;
                $embedding->image_id = $image->id;
                $embedding->embedding = serialize($response['embeddings'][$i]);
                $embedding->save();
            }

        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->route('home')->with('error', 'Image upload failed');
        }

        try {
            $url = \config('api.url') . '/cluster';
            $client = new \GuzzleHttp\Client();
            $embeddings = Embeddings::all();
            $embeddings = $embeddings->map(function($embedding) {
                return unserialize($embedding->embedding);
            });
            $embeddings = $embeddings->toArray();
            //get all current clusters 
            $clusters = Embeddings::all()->pluck('cluster')->toArray();
            $response = $client->request('POST', $url, [
                'json' => [
                    'labeled_data' => $clusters,
                    'embeddings' => $embeddings
                ]
            ]);
            $response = json_decode($response->getBody(), true);
            $clusters = $response['labels'];
            
            $embedding = Embeddings::all();
            foreach ($embedding as $key => $value) {
                $value->cluster = $clusters[$key];
                $value->update();
            }
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->route('home')->with('error', 'Image uploaded but clustering failed');
        }
      
        return redirect()->route('home')->with('success', 'Image uploaded and clustered successfully');
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

    public function clusters(Request $request){
        if(Auth::user() && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to access this page');
        }
        $clusters = Embeddings::all()->groupBy('cluster');

        foreach($clusters as $key => $cluster){
            $clusters[$key] = $cluster->unique('image_id');
        }

        foreach($clusters as $cluster){
            foreach($cluster as $image){
                $image->image = Image::find($image->image_id);
            }
        }
        return view('clusters')->with('clusters', $clusters);
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

        // try {
        //     $url = \config('api.url') . '/cluster';
        //     $client = new \GuzzleHttp\Client();
        //     $embeddings = Embeddings::all();
        //     $embeddings = $embeddings->map(function($embedding) {
        //         return unserialize($embedding->embedding);
        //     });
        //     $embeddings = $embeddings->toArray();
        //     $response = $client->request('POST', $url, [
        //         'json' => [
        //             'embeddings' => $embeddings
        //         ]
        //     ]);
        //     $response = json_decode($response->getBody(), true);
        //     $clusters = $response['labels'];
            
        //     $embedding = Embeddings::all();
        //     foreach ($embedding as $key => $value) {
        //         $value->cluster = $clusters[$key];
        //         $value->update();
        //     }
        // } catch (\Exception $e) {
        //     Log::error($e);
        //     return redirect()->route('home')->with('error', 'Image deleted but clustering failed');
        // }

        return redirect()->route('home')->with('success', 'Image deleted successfully');
    }
}
