<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="text-white alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(Auth::user() && Auth::user()->isAdmin())
                <h1 class="text-2xl font-bold mb-4 text-white">Add Image</h1>
                <div class="bg-grey shadow-md rounded-md p-6">
                    <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="name" class="block text-white font-bold mb-2">Title</label>
                            <input type="text" name="name" id="name" placeholder="Title" class="border rounded-md py-2 px-3 w-full" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-white font-bold mb-2">Description</label>
                            <input type="text" name="description" id="description" placeholder="Description" class="border rounded-md py-2 px-3 w-full" required>
                        </div>
                        <div class="mb-4">
                            <label for="price" class="block text-white font-bold mb-2">Price</label>
                            <input type="number" name="price" id="price" placeholder="Price" class="border rounded-md py-2 px-3 w-full" required>
                        </div>
                        <div class="mb-4">
                            <select name="currency" id="currency" class="border rounded-md py-2 px-3 w-full" required>
                                <option value="usd">USD</option>
                                <option value="gbp">GBP</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="image_path" class="block text-white font-bold mb-2">Image</label>
                            <input type="file" name="image_path" id="image_path" class="text-white border rounded-md py-2 px-3 w-full" required>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Image</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
        </br>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <!-- Loop through the images and display them as cards -->
                @foreach ($images as $image)
                    <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <img src="{{ url('storage/images/'.$image->image_path) }}" alt="{{ $image->name }}" class="w-full h-64 object-cover">
                        <div class="p-6">
                            <h3 class="text-white font-bold text-xl mb-2">{{ $image->name }}</h3>
                            <a href="{{ route('comment.show', ['id' => $image->id]) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

