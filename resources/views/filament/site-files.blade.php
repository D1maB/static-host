<div class="space-y-2 text-sm divide-y divide-gray-200">
    @forelse($files as $file)
        <div class="py-2">
            <a href="{{ 'http://' . strtolower($site->slug) . '.' . config('app.site_base_domain','localhost') . '/' . $file }}" target="_blank" class="block text-primary-600 hover:underline">
                {{ $file }}
            </a>
        </div>
    @empty
        <p class="py-2">No files found.</p>
    @endforelse
</div>
