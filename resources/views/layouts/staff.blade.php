<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard') - Cafe</title>
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
            background-color: rgba(13, 148, 136, 0.1) !important;
            border-color: rgba(20, 184, 166, 0.3) !important;
            color: #ffffff !important;
        }
        .dark input::placeholder,
        .dark textarea::placeholder {
            color: #5eead4 !important;
        }
        .dark form.bg-white,
        .dark div.bg-white {
            background-color: rgba(30, 41, 59, 0.7) !important;
            backdrop-filter: blur(12px);
        }
        .dark label.text-gray-700,
        .dark h1.text-gray-900,
        .dark h2.text-gray-900 {
            color: #fef3c7 !important;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(15, 118, 110, 0.05);
        }
        .dark .glass-card {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body x-data="{
    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
    sidebarOpen: false,
    mobileSidebarOpen: false,
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
}" class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 transition-colors duration-300 overflow-hidden h-screen flex relative">
    
    <!-- Mesh Background -->
    <div class="fixed inset-0 z-[-1] pointer-events-none opacity-40 dark:opacity-20">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-teal-400 dark:bg-teal-900 blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-emerald-400 dark:bg-emerald-900 blur-[120px] mix-blend-multiply dark:mix-blend-lighten"></div>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="mobileSidebarOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileSidebarOpen = false" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Sidebar -->
    <aside class="m-4 mr-0 flex flex-col transition-all duration-300 ease-in-out glass-card rounded-3xl overflow-hidden z-40 fixed lg:relative" 
           :class="[
               mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
               sidebarOpen ? 'w-64' : 'w-20'
           ]"
           style="height: calc(100vh - 2rem);">
        <div class="p-6 flex items-center justify-between">
            <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-3" :class="sidebarOpen ? '' : 'justify-center w-full'">
                <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl shadow-lg shadow-teal-500/30 flex items-center justify-center shrink-0">
                    <span class="text-white font-bold text-lg">C</span>
                </div>
                <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="font-bold text-xl tracking-tight whitespace-nowrap text-teal-900 dark:text-teal-100 uppercase">Staff</span>
            </a>
            <button @click="mobileSidebarOpen = false" class="lg:hidden p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-4 space-y-2 py-4 overflow-y-auto overflow-x-hidden">
            @php
                $navItems = [
                    ['route' => 'staff.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard', 'pattern' => 'staff.dashboard'],
                    ['route' => 'staff.kitchen.index', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Kitchen', 'pattern' => 'staff.kitchen*'],
                    ['route' => 'staff.orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Orders', 'pattern' => 'staff.orders*'],
                    ['route' => 'staff.reservations', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Reservations', 'pattern' => 'staff.reservations*'],
                    ['route' => 'staff.stock.index', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Stock', 'pattern' => 'staff.stock*'],
                ];
            @endphp
            
            @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-200 group {{ request()->routeIs($item['pattern']) ? 'bg-teal-600 text-white shadow-lg shadow-teal-600/30' : 'text-slate-500 hover:bg-teal-50 dark:hover:bg-slate-800 hover:text-teal-600 dark:text-slate-400' }}">
                <div class="shrink-0 flex items-center justify-center transition-transform duration-300 group-hover:scale-110" :class="!sidebarOpen ? 'w-full' : ''">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                </div>
                <span x-show="sidebarOpen" x-transition.opacity class="font-medium whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            <form method="POST" action="{{ route('staff.logout') }}">
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

    <!-- Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        <!-- Header -->
        <header class="h-16 sm:h-20 flex items-center justify-between px-4 sm:px-8 z-10 transition-all duration-300">
            <div class="flex items-center gap-2 sm:gap-4">
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
                
                <h1 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">
                    @yield('title', 'Staff Panel')
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <button @click="toggleTheme" class="p-2.5 rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-md border border-white/20 dark:border-slate-700 hover:shadow-md transition-all">
                    <svg x-show="theme === 'dark'" class="w-5 h-5 text-teal-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="theme === 'light'" class="w-5 h-5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>
                
                <div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
                
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-800 dark:text-white leading-none">{{ Auth::guard('staff')->user()->name }}</p>
                        <p class="text-xs text-slate-400 mt-1 uppercase tracking-tighter">{{ Auth::guard('staff')->user()->role->name }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-teal-100 dark:bg-teal-900/50 flex items-center justify-center text-teal-600 dark:text-teal-400 font-bold shadow-inner">
                        {{ substr(Auth::guard('staff')->user()->name, 0, 1) }}
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

            @yield('content')
        </main>
    </div>
</body>
</html>
