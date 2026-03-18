<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Cafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6, .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dark input:not([type="checkbox"]):not([type="radio"]),
        .dark select,
        .dark textarea {
            background-color: rgba(30, 27, 75, 0.7) !important;
            border-color: rgba(67, 56, 202, 0.5) !important;
            color: #ffffff !important;
        }
        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #a5b4fc !important;
        }
        .dark form.bg-white,
        .dark div.bg-white {
            background-color: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(12px);
        }
        .dark label.text-gray-700,
        .dark h1.text-gray-900,
        .dark h2.text-gray-900 {
            color: #e0e7ff !important;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .dark .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        }
        .dark .glass-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body x-data="{
    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    sidebarOpen: false,
    mobileSidebarOpen: false,
    commandOpen: false,
    toggleTheme() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        const html = document.documentElement;
        if (this.theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        localStorage.setItem('theme', this.theme);
    }
}" @keydown.window.ctrl.k.prevent="commandOpen = !commandOpen" @keydown.window.cmd.k.prevent="commandOpen = !commandOpen" class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-hidden h-screen flex relative">
    
    <!-- Subtle Background for Glassmorphism -->
    <div class="fixed inset-0 z-[-1] pointer-events-none opacity-50 dark:opacity-30">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-slate-200 dark:bg-slate-800 blur-[100px] mix-blend-multiply dark:mix-blend-lighten"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-indigo-100 dark:bg-indigo-900/40 blur-[100px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>
    <!-- Mobile Overlay -->
    <div x-show="mobileSidebarOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileSidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Sidebar (Conditionally Hidden on Reports) -->
    @if(!request()->routeIs('admin.reports.*'))
    <aside class="m-4 mr-0 flex flex-col transition-all duration-300 ease-in-out glass-card rounded-xl overflow-hidden z-40 fixed lg:relative" 
           :class="[
               mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               sidebarOpen ? 'w-64' : 'w-20'
           ]"
           style="height: calc(100vh - 2rem);">
        <div class="p-6 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" :class="sidebarOpen ? '' : 'justify-center w-full'">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-2xl shadow-lg shadow-indigo-500/30 flex items-center justify-center shrink-0">
                    <span class="text-white font-bold text-lg">C</span>
                </div>
                <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="font-bold text-xl tracking-tight whitespace-nowrap text-indigo-900 dark:text-indigo-100 uppercase">Admin</span>
            </a>
            <button @click="mobileSidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-4 space-y-2 py-4 overflow-y-auto overflow-x-hidden">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard', 'pattern' => 'admin.dashboard'],
                    ['route' => 'admin.categories.index', 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16', 'label' => 'Categories', 'pattern' => 'admin.categories.*'],
                    ['route' => 'admin.menu.index', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'label' => 'Menu Items', 'pattern' => 'admin.menu.*'],
                    ['route' => 'admin.tables.index', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', 'label' => 'Tables', 'pattern' => 'admin.tables.*'],
                    ['route' => 'admin.users.index', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Users', 'pattern' => 'admin.users.*'],
                    ['route' => 'admin.orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Orders', 'pattern' => 'admin.orders'],
                    ['route' => 'admin.stock.index', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Stock', 'pattern' => 'admin.stock.*'],
                ];
            @endphp
            
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200 group {{ request()->routeIs($item['pattern']) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-500 hover:bg-indigo-50 dark:hover:bg-slate-800 hover:text-indigo-600 dark:text-slate-400' }}">
                <div class="shrink-0 flex items-center justify-center transition-transform duration-300 group-hover:scale-110" :class="!sidebarOpen ? 'w-full' : ''">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                </div>
                <span x-show="sidebarOpen" x-transition.opacity class="font-medium whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
            @endforeach

            {{-- Super Admin Section --}}
            @if(Auth::guard('admin')->user()->isSuperAdmin())
            <div class="mt-4 pt-4 border-t border-slate-200/50 dark:border-slate-700/50">
                <div x-show="sidebarOpen" class="px-4 mb-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Super Admin</span>
                </div>

                @php
                    $superAdminItems = [
                        ['route' => 'admin.permissions.index', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'label' => 'Permissions', 'pattern' => 'admin.permissions.*'],
                        ['route' => 'admin.audit-logs.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Audit Logs', 'pattern' => 'admin.audit-logs.*'],
                        ['route' => 'admin.approval-requests.index', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Approvals', 'pattern' => 'admin.approval-requests.*'],
                    ];
                    $pendingApprovals = \App\Models\ApprovalRequest::where('status', 'pending')->where(function($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>', now()); })->count();
                @endphp

                @foreach($superAdminItems as $item)
                <a href="{{ route($item['route']) }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200 group {{ request()->routeIs($item['pattern']) ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'text-slate-500 hover:bg-violet-50 dark:hover:bg-slate-800 hover:text-violet-600 dark:text-slate-400' }}">
                    <div class="shrink-0 flex items-center justify-center transition-transform duration-300 group-hover:scale-110" :class="!sidebarOpen ? 'w-full' : ''">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    </div>
                    <span x-show="sidebarOpen" x-transition.opacity class="font-medium whitespace-nowrap">{{ $item['label'] }}</span>
                    @if($item['label'] === 'Approvals' && $pendingApprovals > 0)
                    <span x-show="sidebarOpen" class="ml-auto bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">{{ $pendingApprovals }}</span>
                    @endif
                </a>
                @endforeach
            </div>
            @endif
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors group" :class="sidebarOpen ? '' : 'justify-center'">
                    <div class="shrink-0 transition-transform duration-300 group-hover:-translate-x-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-medium">Logout</span>
                </button>
            </form>
        </div>
    </aside>
    @endif

    <!-- Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative {{ request()->routeIs('admin.reports.*') ? 'w-full' : '' }}">
        <!-- Header -->
        <header class="h-16 sm:h-20 flex items-center justify-between px-4 sm:px-8 z-10 transition-all duration-300">
            <div class="flex items-center gap-2 sm:gap-4">
                @if(request()->routeIs('admin.reports.*'))
                    <!-- Back to Dashboard explicitly for reports -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 p-2 px-4 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 hover:shadow-md transition-all text-slate-700 dark:text-slate-300 font-bold text-sm group">
                        <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Dashboard
                    </a>
                @else
                    <!-- Mobile Menu Button -->
                    <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="lg:hidden p-2 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 hover:shadow-md transition-all">
                        <svg class="w-6 h-6 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <!-- Desktop Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-2 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 hover:shadow-md transition-all">
                        <svg class="w-6 h-6 text-slate-600 dark:text-slate-300 transition-transform duration-300" :class="{'rotate-180': !sidebarOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>
                @endif

                <div class="hidden md:flex items-center gap-2 px-4 py-2 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="text-sm hidden sm:inline">Type <kbd class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-xs shadow-inner">Ctrl K</kbd> to search</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button @click="toggleTheme" class="p-2.5 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 hover:shadow-md transition-all">
                    <svg x-show="theme === 'dark'" class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="theme === 'light'" class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>
                
                <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
                
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-800 dark:text-white leading-none">{{ Auth::guard('admin')->user()->name }}</p>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-tighter">{{ Auth::guard('admin')->user()->role?->name ?? 'Admin' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold shadow-inner">
                        {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Wrapper -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="mb-6 flex items-center p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-2xl shadow-sm">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="mb-6 flex items-center p-4 bg-rose-50 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 rounded-2xl shadow-sm">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            @endif

            @if(session('info'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" class="mb-6 flex items-center p-4 bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 rounded-2xl shadow-sm">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-medium">{{ session('info') }}</p>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Command Palette Hint (Fixed Bottom Right) -->
    <div class="fixed bottom-6 right-6 z-50">
        <button @click="commandOpen = true" class="w-12 h-12 rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/40 flex items-center justify-center hover:scale-110 active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>
    </div>
</body>
</html>
