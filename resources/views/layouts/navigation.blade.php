<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold text-indigo-600">
                        {{ config('app.name', 'Syllabus Management System') }}
                    </a>
                </div>
                <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Dashboard
                    </a>
                    <a href="{{ route('syllabi.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Syllabi
                    </a>
                    @auth
                        @if(auth()->user()->isCdc() || auth()->user()->isAdmin())
                        <a href="{{ route('cdc.programmes.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-700 hover:text-gray-900">
                            Programme Structure
                        </a>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                @auth
                    <span class="text-sm text-gray-700 mr-4">
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-700 hover:text-gray-900">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>

