<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Landing_Page_Website</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles / Tailwind -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Figtree', sans-serif;
            }
            .hero-gradient {
                background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 90%), #0f172a;
            }
            .glass-panel {
                background: rgba(30, 41, 59, 0.7);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .glow-indigo {
                box-shadow: 0 0 40px rgba(99, 102, 241, 0.25);
            }
        </style>
    </head>
    <body class="antialiased hero-gradient text-slate-100 min-h-screen flex flex-col justify-between">
        
        <!-- Navigation Header -->
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white shadow-md shadow-indigo-500/30">
                    DK
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight text-white leading-none">Diki Arya Pratama</h1>
                    <span class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">Analisis Clustering Data K-Means Website</span>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-300">
                <a href="#features" class="hover:text-white transition-colors">Fitur Utama</a>
                <a href="#about" class="hover:text-white transition-colors">Metode Clustering</a>
                <a href="#variables" class="hover:text-white transition-colors">Variabel</a>
            </nav>

            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition-all shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30">
                            Masuk Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm transition-all shadow-md shadow-indigo-500/20 hover:shadow-indigo-500/30">
                            Log in
                        </a>
                    @endauth
                @endif
            </div>
        </header>

        <!-- Main Landing Content -->
        <main class="flex-grow flex flex-col items-center justify-center px-6 py-12">
            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left Column: Copywriting -->
                <div class="space-y-8 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs font-semibold tracking-wide">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        K-Means Clustering Real-Time
                    </div>

                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                        Analisis Clustering<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">
                            K-Means Web
                        </span>
                    </h2>

                    <p class="text-slate-300 text-base sm:text-lg max-w-xl leading-relaxed">
                        Dengan adanya sistem yang dikembangkan, proses analisis data diharapkan dapat dilakukan secara lebih efisien serta mampu memberikan informasi yang mendukung pengambilan keputusan secara berbasis data.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold text-base text-center transition-all shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 hover:translate-y-[-1px]">
                                Eksplorasi Sekarang
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold text-base text-center transition-all shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 hover:translate-y-[-1px]">
                                Eksplorasi Sekarang
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                            <a href="#features" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-slate-900/60 hover:bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white text-base font-bold text-center transition-all shadow-sm">
                                Pelajari Fitur
                            </a>
                        @endauth
                    </div>

                    <!-- Small Stat Badges -->
                    <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-800 max-w-md">
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">21+</div>
                            <div class="text-xs text-slate-400 font-medium">Kecamatan LOTIM</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">4</div>
                            <div class="text-xs text-slate-400 font-medium">Kluster Utama</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-extrabold text-white">99%</div>
                            <div class="text-xs text-slate-400 font-medium">Data Dinamis</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visual Mockup Showcase -->
                <div class="relative w-full flex justify-center items-center">
                    <div class="absolute w-72 h-72 rounded-full bg-indigo-500/20 blur-3xl -top-10 -left-10"></div>
                    <div class="absolute w-72 h-72 rounded-full bg-purple-500/20 blur-3xl -bottom-10 -right-10"></div>
                    
                    <div class="glass-panel w-full p-2.5 rounded-3xl glow-indigo rotate-1 hover:rotate-0 transition-transform duration-500 ease-out z-10">
                        <div class="bg-slate-950/40 border border-slate-900 rounded-[20px] overflow-hidden shadow-inner aspect-[16/10] relative flex items-center justify-center p-6 text-center">
                            <!-- Visual Dashboard Concept -->
                            <div class="space-y-4 w-full">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-3 h-3 rounded-full bg-rose-500/70"></span>
                                        <span class="w-3 h-3 rounded-full bg-amber-500/70"></span>
                                        <span class="w-3 h-3 rounded-full bg-emerald-500/70"></span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-mono">dashboard_mockup.active</span>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="col-span-2 h-36 bg-slate-900/60 rounded-xl border border-slate-800/60 flex flex-col justify-between p-3">
                                        <span class="text-[9px] text-indigo-400 font-bold uppercase tracking-wider text-left block">PETA SEBARAN KLUSTER</span>
                                        <!-- Mock Map Rings -->
                                        <div class="relative w-full h-20 flex items-center justify-center overflow-hidden">
                                            <div class="absolute w-12 h-12 rounded-full border border-blue-500/30 bg-blue-500/5 animate-pulse"></div>
                                            <div class="absolute w-8 h-8 rounded-full border border-emerald-500/30 bg-emerald-500/5"></div>
                                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 absolute top-6 left-12 border-2 border-slate-950 shadow"></span>
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 absolute bottom-4 right-16 border-2 border-slate-950 shadow"></span>
                                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 absolute top-8 right-8 border-2 border-slate-950 shadow"></span>
                                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 absolute bottom-8 left-20 border-2 border-slate-950 shadow"></span>
                                        </div>
                                    </div>
                                    <div class="h-36 bg-slate-900/60 rounded-xl border border-slate-800/60 flex flex-col justify-between p-3">
                                        <span class="text-[9px] text-indigo-400 font-bold uppercase tracking-wider text-left block">DISTRIBUSI</span>
                                        <div class="w-14 h-14 rounded-full border-[8px] border-slate-800 border-t-indigo-500 border-r-cyan-500 mx-auto"></div>
                                        <span class="text-[8px] text-slate-500 font-semibold block">4 Kelompok Terbentuk</span>
                                    </div>
                                </div>
                                <div class="h-16 bg-slate-900/60 rounded-xl border border-slate-800/60 p-3 flex items-center justify-between text-left">
                                    <div>
                                        <span class="text-[8px] text-slate-500 uppercase font-bold tracking-wider">Metode Perhitungan</span>
                                        <div class="text-xs font-bold text-white">PHP Native K-Means</div>
                                    </div>
                                    <span class="px-2 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-[9px] text-emerald-400 font-bold">Akurasi 100%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Features / Detail section -->
        <section id="features" class="max-w-7xl mx-auto px-6 py-16 w-full border-t border-slate-800/50">
            <h3 class="text-center font-extrabold text-2xl sm:text-3xl text-white mb-12">Fitur Unggulan Sistem</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Feature 1 -->
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-left space-y-4 hover:border-indigo-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.485V6.702a2 2 0 011.553-1.954l8.944-2.236a2 2 0 011.006 0l8.944 2.236A2 2 0 0121 6.702v8.783a2 2 0 01-1.553 1.954L15 20M9 20l3-2.667M9 20L12 17.5M15 20L12 17.333M15 20l-3-2.5m0-13.833V17.5" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-white">Peta Wilayah Interaktif</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Mengintegrasikan Leaflet.js dengan marker koordinat presisi dan visualisasi overlay lingkaran kluster yang responsif.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-left space-y-4 hover:border-indigo-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-white">Algoritma K-Means Asli</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Mengimplementasikan model clustering K-Means secara murni dalam PHP, didukung normalisasi Min-Max dan inisialisasi K-Means++ untuk performa yang optimal.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 rounded-2xl bg-slate-900/40 border border-slate-800/60 text-left space-y-4 hover:border-indigo-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-white">Variabel & Filter Dinamis</h4>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Saring data berdasarkan Provinsi dan Tahun, serta tentukan sendiri variabel indikator mana yang ingin dilibatkan dalam proses komputasi kluster.
                    </p>
                </div>

            </div>
        </section>

        <!-- Footer -->
        <footer class="w-full max-w-7xl mx-auto px-6 py-8 border-t border-slate-850 text-center text-xs text-slate-500">
            &copy; 2026 Website Clustering Kependudukan Digital. Dibuat dengan Laravel, Tailwind CSS, Leaflet.js dan ApexCharts.
        </footer>

    </body>
</html>
