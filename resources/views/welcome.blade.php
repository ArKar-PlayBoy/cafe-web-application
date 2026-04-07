<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Welcome - Cafe</title>
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
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }
        .dark .glass-card {
            background: rgba(17, 24, 39, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="bg-stone-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">
    <nav class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-teal-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">C</span>
                        </div>
                        <span class="font-semibold text-xl text-teal-700 dark:text-teal-400">Cafe</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <x-weather-navbar />
                    <button id="theme-toggle" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5 hidden dark:block text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg class="w-5 h-5 block dark:hidden text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="overflow-hidden">
        <!-- Hero Section -->
        <div class="relative min-h-[90vh] flex items-center justify-center">
            <!-- Background mesh -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none z-[-1]">
                <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] rounded-full bg-teal-200 dark:bg-teal-900/30 blur-[120px] mix-blend-multiply dark:mix-blend-screen opacity-70"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-emerald-200 dark:bg-emerald-900/30 blur-[120px] mix-blend-multiply dark:mix-blend-screen opacity-70"></div>
                <div class="absolute top-[40%] left-[60%] w-[30%] h-[30%] rounded-full bg-purple-200 dark:bg-purple-900/20 blur-[100px] mix-blend-multiply dark:mix-blend-screen opacity-50"></div>
            </div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative z-10 w-full">
                <div class="text-center">
                    <div class="inline-flex items-center gap-3 bg-white/60 dark:bg-gray-800/60 backdrop-blur-md rounded-full px-5 py-2 mb-8 shadow-sm border border-purple-200 dark:border-purple-500/30 hover:shadow-md hover:border-purple-300 dark:hover:border-purple-500/60 transition-all duration-300 cursor-default">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-purple-500"></span>
                        </span>
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span class="text-sm text-purple-700 dark:text-purple-300 font-medium">Meet AI Barista - Your Personal Coffee Expert</span>
                    </div>

                    <h1 class="text-6xl md:text-7xl lg:text-8xl font-heading font-extrabold text-gray-900 dark:text-white mb-6 tracking-tight">
                        Crafted for the <br class="hidden md:block" />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-400">Coffee Connoisseur</span>
                    </h1>
                    <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-10 max-w-3xl mx-auto font-light leading-relaxed">Experience the finest coffee, delicious pastries, and a warm atmosphere. Your perfect escape awaits.</p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-8">
                        <a href="{{ route('menu') }}" class="group relative px-8 py-4 bg-teal-600 text-white rounded-full font-medium text-lg overflow-hidden shadow-lg shadow-teal-600/30 hover:shadow-teal-600/50 transition-all duration-300 w-full sm:w-auto text-center hover:-translate-y-1">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                Order Now
                                <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </a>
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-white/50 dark:bg-gray-800/50 text-gray-800 dark:text-white border border-gray-200 dark:border-gray-700 rounded-full font-medium text-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-300 shadow-sm backdrop-blur-sm w-full sm:w-auto text-center hover:-translate-y-1">
                            Join Rewards
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="relative py-24 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="glass-card rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-500">
                        <div class="w-20 h-20 bg-gradient-to-br from-teal-100 to-emerald-200 dark:from-teal-900/50 dark:to-emerald-800/50 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:rotate-6 transition-transform duration-500 shadow-inner">
                            <svg class="w-10 h-10 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-heading font-bold mb-3 text-gray-900 dark:text-white">Premium Quality</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Ethically sourced, freshly roasted beans from the world's finest coffee regions.</p>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="glass-card rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-500 md:translate-y-8">
                        <div class="w-20 h-20 bg-gradient-to-br from-orange-100 to-amber-200 dark:from-orange-900/50 dark:to-amber-800/50 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:-rotate-6 transition-transform duration-500 shadow-inner">
                            <svg class="w-10 h-10 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-heading font-bold mb-3 text-gray-900 dark:text-white">Fresh Pastries</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">Artisanal baked goods prepared daily by our expert pastry chefs. The perfect pairing.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="glass-card rounded-3xl p-8 text-center group hover:-translate-y-2 transition-transform duration-500">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-200 dark:from-blue-900/50 dark:to-indigo-800/50 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:rotate-6 transition-transform duration-500 shadow-inner">
                            <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-heading font-bold mb-3 text-gray-900 dark:text-white">Cozy Atmosphere</h3>
                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed text-sm">A warm, inviting space designed for focus, connection, and relaxation.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter/CTA -->
        <div class="py-24 relative z-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="glass-card rounded-[3rem] p-12 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500/10 to-emerald-500/10 pointer-events-none"></div>
                    <h2 class="text-4xl md:text-5xl font-heading font-bold mb-6 text-gray-900 dark:text-white relative z-10">Stay in the Loop</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-xl mx-auto relative z-10">Join our community for exclusive offers, early access to new blends, and insider coffee tips.</p>
                    
                    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="max-w-md mx-auto relative z-10 flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input type="email" name="email" placeholder="Enter your email" class="w-full px-6 py-4 rounded-full bg-white/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white dark:focus:bg-gray-800 transition-all text-gray-800 dark:text-white placeholder-gray-500 shadow-sm backdrop-blur-sm">
                        <button type="submit" class="px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-full font-medium shadow-lg hover:shadow-xl transition-all duration-300 whitespace-nowrap hover:-translate-y-0.5">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-stone-100 dark:bg-gray-900 py-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400">
            <div class="flex justify-center gap-4 mb-4">
                <a href="{{ route('admin.login') }}" class="hover:text-teal-600 dark:hover:text-teal-400">Admin</a>
                <span>|</span>
                <a href="{{ route('staff.login') }}" class="hover:text-teal-600 dark:hover:text-teal-400">Staff</a>
            </div>
            <p>&copy; 2026 Cafe. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const html = document.documentElement;
                const isDark = html.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });
        }
    </script>

    @auth
    <x-ai-barista-button />
    <x-ai-chat-modal />
    <script src="{{ asset('js/ai-chat.js') }}"></script>
    @endauth
</body>
</html>
