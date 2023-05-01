<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="text-white alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
        </br>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-5">
                <!-- Loop through the images and display them as cards -->
                @foreach ($clusters as $cluster)
                    <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg grid grid-cols-1 sm:grid-cols-4">
                        @foreach ($cluster as $image)
                            <img src="{{ url('storage/images/'.$image->image->image_path) }}" alt="{{ $image->image->name }}" class="w-64 h-64 object-cover">
                            <div class="p-6">
                                <h3 class="text-white font-bold text-xl mb-2">{{ $image->image->name }}</h3>
                                <a href="{{ route('comment.show', ['id' => $image->image->id]) }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">View</a>
                            </div>
                        @endforeach
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>

