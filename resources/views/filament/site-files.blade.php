<div class="space-y-2 text-sm">
    @forelse($files as $file)
        <a href="{{ 'http://' . strtolower($site->slug) . '.' . config('app.site_base_domain','localhost') . '/' . $file }}" target="_blank" class="block text-primary-600 hover:underline">
            {{ $file }}
        </a>
    @empty
        <p>No files found.</p>
    @endforelse
</div>
