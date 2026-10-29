<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-6">
    <a href="{{ route('admin.dashboard') }}" class="text-indigo-600 mb-4 inline-block">&larr; Back</a>
    <h1 class="text-2xl font-bold mb-4">Users</h1>
    @if(session('success'))<div class="bg-green-100 p-2 mb-3">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="bg-red-100 p-2 mb-3">{{ session('error') }}</div>@endif
    <div class="bg-white shadow rounded">
      <table class="w-full">
        <thead><tr class="border-b"><th class="p-3 text-left">Name</th><th class="p-3 text-left">Email</th><th class="p-3 text-left">Role</th><th class="p-3"></th></tr></thead>
        <tbody>
          @foreach($users as $user)
          <tr class="border-b">
            <td class="p-3">{{ $user->name }}</td>
            <td class="p-3">{{ $user->email }}</td>
            <td class="p-3">{{ $user->role }}</td>
            <td class="p-3 text-right">
              <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button class="text-red-600">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
  </div>
</body>
</html>
