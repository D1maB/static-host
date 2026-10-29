<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen">
  <div class="max-w-5xl mx-auto p-6">
    <div class="flex justify-between mb-6">
      <h1 class="text-2xl font-bold">Admin Dashboard</h1>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-red-600">Logout</button></form>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <a href="{{ route('admin.users') }}" class="bg-white shadow rounded p-6 block">Manage Users</a>
      <a href="{{ route('admin.sites') }}" class="bg-white shadow rounded p-6 block">Manage Sites</a>
    </div>
  </div>
</body>
</html>
