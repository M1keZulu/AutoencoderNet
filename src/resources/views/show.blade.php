<x-app-layout>
    <div class="container mx-auto p-4 bg-gray">
        @if(session('success'))
            <div class="text-white alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        </br>
        @if(Auth::user() && Auth::user()->isAdmin())
        <div class="bg-grey shadow-md rounded-md p-6">
            <img src="{{ url('storage/images/'.$image->image_path) }}" alt="{{ $image->name }}">
            </br>
            <form action="{{ route('admin.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $image->id }}">
                <div class="mb-4">
                    <label for="name" class="block text-white font-bold mb-2">Name</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="{{ $image->name }}">
                </div>
                <div class="mb-5">
                    <label for="description" class="block text-white font-bold mb-2">Description</label>
                    <input type="text" name="description" class="form-control" id="description" placeholder="{{ $image->description }}">
                </div>
                <div class="mb-4">
                    <label for="price" class="block text-white font-bold mb-2">Price</label>
                    <input type="number" name="price" class="form-control" id="price" placeholder="{{ $image->price }}">
                </div>
                <div class="mb-4">
                    <label for="currency" class="block text-white font-bold mb-2">Currency</label>
                    <select name="currency" id="currency" class="form-control">
                        <option value="usd" {{ $image->currency == 'usd' ? 'selected' : '' }}>USD</option>
                        <option value="gbp" {{ $image->currency == 'gbp' ? 'selected' : '' }}>GBP</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md mt-2">Edit</button>
            </form>
            <form action="{{ route('admin.destroy') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $image->id }}">
                <button type="submit" class="bg-red-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md mt-2">Delete</button>
            </form>
        </div>
        @else
            <img src="{{ url('storage/images/'.$image->image_path) }}" alt="{{ $image->name }}">
            </br>
            <div class="caption">
                <h4 class="text-white"><strong>Name:</strong> {{ $image->name }}</h4>
                <p class="text-white"><strong>Description:</strong> {{ $image->description }}</p>
                <p class="text-white"><strong>Price:</strong> 
                    @if($image->currency == 'usd')
                        ${{ $image->price }}
                    @elseif($image->currency == 'gbp')
                        £{{ $image->price }}
                    @endif
                </p>
                <p class="text-white"><strong>Views:</strong> {{ $image->views }}</p>
                <p class="text-white"><strong>Last Viewed:</strong> {{ $image->updated_at }}</p>

            </div>
        @endif
        </br>
        <h2 class="text-white mt-4 mb-2 text-lg font-bold">Comments</h2>
        @auth
            <form action="{{ route('comment.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $image->id }}">
                <input type="text" name="content" placeholder="Add a comment..." class="border border-gray-400 py-2 px-3 rounded-md w-full">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-md mt-2">Add Comment</button>
            </form>
        @endauth
        @guest
            <p class="text-white">User must be logged in to comment</p>
        @endguest
        </br>
        <ul class="list-disc list-inside">
            @foreach ($comments as $comment)
                <li class="text-white">{{ $comment->user->name}} [{{ $comment->created_at }}]: {{ $comment->content }}</li>
            @endforeach
        </ul>
    </div>
</x-app-layout>