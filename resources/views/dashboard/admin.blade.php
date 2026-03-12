<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Users</div>
                    <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Syllabi</div>
                    <div class="text-3xl font-bold">{{ $stats['total_syllabi'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Pending Approval</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['pending_approval'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Approved</div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['approved_syllabi'] }}</div>
                </div>
            </div>

            <!-- Admin Links -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <h3 class="text-lg font-medium mb-4">Administration</h3>
                <div class="flex space-x-4 flex-wrap gap-y-2">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Manage Users</a>
                    <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">Manage Departments</a>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">Audit Logs</a>
                    <a href="{{ route('admin.analytics.index') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Analytics</a>
                    <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">Settings</a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Recent Activity</h3>
                    @if($recentActivity->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentActivity as $log)
                                    <tr>
                                        <td class="px-6 py-4">{{ $log->user?->name ?? 'System' }}</td>
                                        <td class="px-6 py-4">{{ $log->action }}</td>
                                        <td class="px-6 py-4">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No recent activity.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
