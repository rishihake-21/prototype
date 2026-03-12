<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manage Users
            </h2>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                + Add User
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex space-x-4">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300">
                    <select name="role" class="rounded-md border-gray-300">
                        <option value="">All Roles</option>
                        @foreach(\App\Models\User::roles() as $value => $label)
                        <option value="{{ $value }}" {{ request('role') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-md border-gray-300">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md">Filter</button>
                </form>
            </div>

            <!-- Users List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($users->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Departments</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr class="@if($user->status === 'pending') bg-yellow-50 @endif">
                                        <td class="px-6 py-4">
                                            {{ $user->name }}
                                            @if($user->status === 'pending')
                                                <span class="ml-2 px-2 py-1 text-xs bg-yellow-200 text-yellow-800 rounded-full">Pending Approval</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $user->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($user->isAdmin()) bg-red-100 text-red-800
                                                @elseif($user->isHod()) bg-blue-100 text-blue-800
                                                @elseif($user->isCdc()) bg-purple-100 text-purple-800
                                                @elseif($user->isObserver()) bg-gray-100 text-gray-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                {{ \App\Models\User::roles()[$user->role] ?? $user->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">{{ $user->departments->pluck('name')->join(', ') ?: '—' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full
                                                @if($user->status === 'active') bg-green-100 text-green-800
                                                @elseif($user->status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 space-x-2">
                                            @if($user->status === 'pending')
                                                <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white text-sm px-3 py-1 rounded">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this user?');">
                                                    @csrf
                                                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                                        Reject
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                @if($user->id !== auth()->id())
                                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Are you sure you want to {{ $user->status === 'active' ? 'deactivate' : 'activate' }} this user?');">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-{{ $user->status === 'active' ? 'red' : 'green' }}-600 hover:text-{{ $user->status === 'active' ? 'red' : 'green' }}-900">
                                                            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No users found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
