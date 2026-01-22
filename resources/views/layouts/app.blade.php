<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ProdPlan System</title>

   <!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Alpine.js Plugins (harus sebelum Alpine core) -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

<!-- Alpine.js Core -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Sidebar styles */
        .sidebar-gradient {
            background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
        }

        .sidebar-nav-container {
            height: calc(100vh - 80px - 80px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Modal animations */
        .modal-backdrop {
            backdrop-filter: blur(0px);
            -webkit-backdrop-filter: blur(0px);
            background-color: rgba(0, 0, 0, 0);
            transition: backdrop-filter 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .modal-backdrop.modal-fade-in {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            background-color: rgba(0, 0, 0, 0.3);
        }

        .modal-backdrop > div {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-backdrop.modal-fade-in > div {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* Menu item styles */
        .menu-item {
            position: relative;
            margin: 0.5rem 0;
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: white;
            transform: scaleY(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .menu-item.active::before {
            transform: scaleY(1);
        }

        .menu-item.active {
            background: white;
            color: black;
        }

        .menu-item:not(.active):hover {
            background: rgba(255, 255, 255, 0.08);
        }

        /* Logout button */
        .logout-btn {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(153, 27, 27, 0.1) 100%);
            border: 1px solid rgba(220, 38, 38, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.2) 0%, rgba(153, 27, 27, 0.2) 100%);
            border-color: rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: true }">
        
        <!-- Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 w-64 sidebar-gradient text-white transform transition-transform duration-300 ease-in-out shadow-2xl"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-center h-20 border-b border-gray-800 border-opacity-50 px-4">
                <div class="flex items-center space-x-3">
                    <div 
                        class="flex items-center justify-center"
                        style="--logo-size: 70px; width: var(--logo-size); height: var(--logo-size);"
                    >
                        <img 
                            src="{{ asset('images/logostep.png') }}"
                            alt="ProdPlan Logo"
                            class="w-full h-full object-contain"
                        >
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-wide">ProdPlan</h1>
                        <p class="text-xs text-gray-400">System</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
<div class="sidebar-nav-container px-3 space-y-1">
    <nav class="mt-4 space-y-1 pb-4">
        
        <!-- DATA MASTER -->
        <div x-data="{ open: {{ request()->routeIs('mesin.*') || request()->routeIs('items.*') ? 'true' : 'false' }} }">
            <button 
                @click="open = !open"
                class="menu-item w-full flex items-center justify-between px-4 py-2.5 text-gray-300 hover:bg-gray-700"
            >
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="font-semibold text-sm">Data Master</span>
                </div>

                <svg class="w-4 h-4 transform transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                <!-- Master Mesin -->
                <a href="{{ route('mesin.index') }}"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300
                        {{ request()->routeIs('mesin.*') ? 'active' : '' }}">
                    Master Mesin
                </a>

                <!-- Items -->
                <a href="{{ route('items.index') }}"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300
                        {{ request()->routeIs('items.*') ? 'active' : '' }}">
                    Items
                </a>
            </div>
        </div>

        <!-- PRODUCTION -->
        <div x-data="{ open: {{ request()->routeIs('plannings.*') || request()->routeIs('preview-andon.*') ? 'true' : 'false' }} }">
            <button 
                @click="open = !open"
                class="menu-item w-full flex items-center justify-between px-4 py-2.5 text-gray-300 hover:bg-gray-700"
            >
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="font-semibold text-sm">Production</span>
                </div>

                <svg class="w-4 h-4 transform transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                <!-- Planning -->
                <a href="{{ route('plannings.index') }}"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300
                        {{ request()->routeIs('plannings.*') ? 'active' : '' }}">
                    Planning
                </a>
                
                <!-- Preview Andon -->
                <a href="{{ route('preview-andon.index') }}"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300
                        {{ request()->routeIs('preview-andon.*') ? 'active' : '' }}">
                    Preview Andon
                </a>
            </div>
        </div>

        <!-- ANDON (Menu Baru) -->
        <div x-data="{ open: {{ request()->routeIs('andon.*') ? 'true' : 'false' }} }">
            <button 
                @click="open = !open"
                class="menu-item w-full flex items-center justify-between px-4 py-2.5 text-gray-300 hover:bg-gray-700"
            >
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="font-semibold text-sm">Andon</span>
                </div>

                <svg class="w-4 h-4 transform transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="ml-8 mt-1 space-y-1">
                <!-- Preview Andon (dua tempat) -->
                <a href="{{ route('preview-andon.index') }}"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300
                        {{ request()->routeIs('preview-andon.*') ? 'active' : '' }}">
                    Preview Andon
                </a>
                
                <!-- Real-time Andon (untuk nanti) -->
                <a href="#"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300 opacity-50 cursor-not-allowed">
                    Real-time Andon
                    <span class="ml-2 px-1.5 py-0.5 text-xs bg-yellow-500 text-white rounded">Soon</span>
                </a>
                
                <!-- Andon History (untuk nanti) -->
                <a href="#"
                class="menu-item flex items-center px-4 py-2 text-sm text-gray-300 opacity-50 cursor-not-allowed">
                    Andon History
                    <span class="ml-2 px-1.5 py-0.5 text-xs bg-yellow-500 text-white rounded">Soon</span>
                </a>
            </div>
        </div>

    </nav>
</div>

            <!-- Logout Button -->
            <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-800 border-opacity-50 bg-gradient-to-t from-black to-transparent">
                <button onclick="confirmLogout()" 
                        class="logout-btn flex items-center w-full px-4 py-3 rounded-lg text-gray-300 transition-all duration-300">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="font-semibold text-sm">Logout</span>
                </button>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-0'">
            <!-- Header -->
            <header class="bg-white shadow-sm z-40 sticky top-0">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Menu Toggle -->
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="text-gray-600 hover:text-black focus:outline-none transition-all duration-200 hover:scale-110"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-4">
                        <!-- Date & Time -->
                        <div class="flex items-center space-x-2 text-gray-600">
                            <div class="text-sm">
                                <span id="current-date" class="font-medium"></span>
                                <span class="mx-2">|</span>
                                <span id="current-time" class="font-semibold"></span>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-3 hover:bg-gray-50 px-4 py-2 rounded-xl transition-all duration-200"
                            >
                                <div class="w-9 h-9 bg-gradient-to-br from-gray-800 to-black text-white rounded-full flex items-center justify-center font-bold ring-2 ring-gray-200">
                                    {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                                </div>
                                <div class="text-left">
                                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->nama }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->user()->role_label }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" 
                                     :class="open ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown -->
                            <div 
                                x-show="open"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg py-2 z-50 border border-gray-100"
                                x-cloak
                            >
                                <button onclick="confirmLogout()" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 px-6 py-4 text-center">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} <strong class="text-black">ProdPlan System</strong> - All rights reserved.
                </p>
            </footer>
        </div>
    </div>

    <script>
        // Update date & time
        function updateDateTime() {     
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateStr = now.toLocaleDateString('id-ID', options);
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            document.getElementById('current-date').textContent = dateStr;
            document.getElementById('current-time').textContent = timeStr;
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Apakah Anda yakin ingin keluar dari sistem?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E4080A',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>