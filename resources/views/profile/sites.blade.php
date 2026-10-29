<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sites</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-6">
    <a href="{{ route('admin.dashboard') }}" class="text-amber-600 mb-4 inline-block">&larr; Back</a>
    <h1 class="text-2xl font-bold mb-4">Sites</h1>
    @if(session('success'))<div class="bg-green-100 p-2 mb-3">{{ session('success') }}</div>@endif
    <div class="bg-white shadow rounded">
      <table class="w-full">
        <thead><tr class="border-b"><th class="p-3 text-left">Name</th><th class="p-3 text-left">Owner</th><th class="p-3 text-left">Slug</th><th class="p-3 text-left">Active</th><th class="p-3"></th></tr></thead>
        <tbody>
          @foreach($sites as $site)
          <tr class="border-b">
            <td class="p-3">{{ $site->name }}</td>
            <td class="p-3">{{ $site->user->email ?? '' }}</td>
            <td class="p-3"><a href="http://{{ strtolower($site->slug) }}.localhost" target="_blank" class="text-amber-600">{{ strtolower($site->slug) }}.localhost</a></td>
            <td class="p-3">{{ $site->is_active ? 'Yes' : 'No' }}</td>
            <td class="p-3 text-right space-x-3">
              <form method="POST" action="{{ route('admin.sites.toggle', $site) }}" class="inline">@csrf @method('PATCH')<button class="text-amber-600">Toggle</button></form>
              <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $sites->links() }}</div>
  </div>
</body>
</html>
