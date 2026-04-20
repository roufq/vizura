<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vizura UI/UX Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-dark {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .neon-glow {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">

    <!-- Header Section -->
    <header class="py-12 bg-white border-b border-slate-200">
        <div class="max-row px-8 max-w-7xl mx-auto text-center">
            <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600 mb-4">
                Vizura UI/UX Transformation
            </h1>
            <p class="text-slate-500 text-lg max-w-2xl mx-auto">
                Eksplorasi desain masa depan untuk sistem POS dan Keuangan Vizura. Pilih arah visual yang paling sesuai dengan brand Anda.
            </p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-8 py-16 space-y-24">

        <!-- Theme 1: Modern Glassmorphism (Dark) -->
        <section id="theme-dark" class="space-y-8">
            <div class="flex items-center gap-4">
                <span class="px-4 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-full">STYLE 01</span>
                <h2 class="text-2xl font-bold">Modern Glassmorphism (Dark)</h2>
            </div>
            
            <div class="bg-slate-900 p-8 rounded-3xl overflow-hidden relative">
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-violet-500/20 rounded-full blur-3xl"></div>

                <div class="grid grid-cols-12 gap-6 relative">
                    <!-- Sidebar Mockup -->
                    <div class="col-span-3 glass-dark p-6 rounded-2xl space-y-8">
                        <div class="text-white font-bold text-xl tracking-tight">VIZURA</div>
                        <nav class="space-y-4">
                            <div class="flex items-center gap-3 text-indigo-400 bg-indigo-500/10 p-3 rounded-xl border border-indigo-500/20">
                                <div class="w-5 h-5 bg-indigo-400 rounded-md"></div>
                                <span class="font-medium">Dashboard</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-400 p-3">
                                <div class="w-5 h-5 bg-slate-700 rounded-md"></div>
                                <span>Penjualan</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-400 p-3">
                                <div class="w-5 h-5 bg-slate-700 rounded-md"></div>
                                <span>Stok</span>
                            </div>
                        </nav>
                    </div>

                    <!-- Main Content Mockup -->
                    <div class="col-span-9 space-y-6">
                        <div class="grid grid-cols-3 gap-6">
                            <div class="glass-dark p-6 rounded-2xl relative overflow-hidden">
                                <p class="text-slate-400 text-sm mb-1">Total Sales</p>
                                <h3 class="text-white text-2xl font-bold">Rp 12,4M</h3>
                                <div class="mt-4 text-xs text-emerald-400 flex items-center gap-1">
                                    <span>+14.5%</span> than last month
                                </div>
                            </div>
                            <div class="glass-dark p-6 rounded-2xl">
                                <p class="text-slate-400 text-sm mb-1">Transactions</p>
                                <h3 class="text-white text-2xl font-bold">1,280</h3>
                                <div class="mt-4 text-xs text-slate-500">Normal traffic</div>
                            </div>
                            <div class="glass-dark p-6 rounded-2xl">
                                <p class="text-slate-400 text-sm mb-1">Low Stock</p>
                                <h3 class="text-white text-2xl font-bold">12 Items</h3>
                                <div class="mt-4 text-xs text-rose-400 flex items-center gap-1">
                                    Critical priority
                                </div>
                            </div>
                        </div>

                        <div class="glass-dark p-8 rounded-2xl h-64 flex flex-col justify-end">
                            <div class="flex items-end gap-2 h-full">
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 40%"></div>
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 60%"></div>
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 45%"></div>
                                <div class="bg-indigo-400 w-full rounded-t-lg neon-glow" style="height: 85%"></div>
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 70%"></div>
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 55%"></div>
                                <div class="bg-indigo-500 w-full rounded-t-lg" style="height: 50%"></div>
                            </div>
                            <div class="border-t border-slate-800 pt-4 mt-2 flex justify-between text-[10px] text-slate-500 uppercase tracking-widest">
                                <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Theme 2: Minimalist Clean (Light) -->
        <section id="theme-light" class="space-y-8">
            <div class="flex items-center gap-4">
                <span class="px-4 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-full">STYLE 02</span>
                <h2 class="text-2xl font-bold">Minimalist Clean (Light)</h2>
            </div>
            
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-xl overflow-hidden relative">
                <div class="grid grid-cols-12 gap-8">
                    <!-- Sidebar Mockup -->
                    <div class="col-span-3 border-r border-slate-100 pr-8 space-y-10">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold">V</div>
                            <div class="font-bold text-slate-800">Vizura</div>
                        </div>
                        <nav class="space-y-2">
                            <div class="flex items-center gap-3 text-emerald-600 font-semibold bg-emerald-50 px-4 py-3 rounded-xl">
                                <span>Dashboard</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-500 px-4 py-3">
                                <span>Penjualan</span>
                            </div>
                            <div class="flex items-center gap-3 text-slate-500 px-4 py-3">
                                <span>Laporan Keuangan</span>
                            </div>
                        </nav>
                    </div>

                    <!-- Main Content Mockup -->
                    <div class="col-span-9 space-y-8">
                        <div class="flex justify-between items-center">
                            <h4 class="font-bold text-xl">Overview Analytics</h4>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-500">April 2026</span>
                                <div class="w-6 h-6 bg-slate-100 rounded flex items-center justify-center text-[10px]">▼</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-4">
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between mb-4">
                                        <span class="text-sm text-slate-500">Gross Income</span>
                                        <span class="text-emerald-500 font-bold bg-emerald-50 px-2 py-0.5 rounded-full text-[10px]">+ 8%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-slate-800">Rp 45.3M</div>
                                </div>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between mb-4">
                                        <span class="text-sm text-slate-500">Operating Expenses</span>
                                        <span class="text-rose-500 font-bold bg-rose-50 px-2 py-0.5 rounded-full text-[10px]">+ 12%</span>
                                    </div>
                                    <div class="text-2xl font-bold text-slate-800">Rp 12.8M</div>
                                </div>
                            </div>
                            <div class="p-8 bg-slate-900 rounded-3xl text-white flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-slate-400 text-sm">Store Performance</p>
                                        <h5 class="text-lg font-bold">Cabang Jakarta Pusat</h5>
                                    </div>
                                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center">
                                        <div class="w-5 h-5 bg-emerald-500 rounded-sm rotate-45"></div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-2xl font-bold">98.4%</p>
                                    <p class="text-xs text-slate-500 uppercase">Target Achievement</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Theme 3: Vibrant Dynamic (Modern) -->
        <section id="theme-vibrant" class="space-y-8">
            <div class="flex items-center gap-4">
                <span class="px-4 py-1.5 bg-rose-600 text-white text-xs font-bold rounded-full">STYLE 03</span>
                <h2 class="text-2xl font-bold">Vibrant Dynamic (Modern)</h2>
            </div>
            
            <div class="bg-indigo-600 p-12 rounded-[3rem] overflow-hidden relative">
                <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-br from-indigo-500 to-rose-500 opacity-50"></div>
                
                <div class="relative grid grid-cols-12 gap-8 items-center">
                    <div class="col-span-5 text-white space-y-6">
                        <h3 class="text-5xl font-black leading-tight italic uppercase tracking-tighter italic">BOOST YOUR BUSINESS</h3>
                        <p class="text-indigo-100 text-lg">Visual yang dinamis dan berenergi untuk mempercepat fokus tim Anda dalam mengejar target harian.</p>
                        <div class="flex gap-4">
                            <button class="bg-white text-indigo-600 px-8 py-3 rounded-2xl font-bold shadow-xl">COBA POS BARU</button>
                            <button class="border border-white/30 text-white px-8 py-3 rounded-2xl font-bold">LIHAT LAPORAN</button>
                        </div>
                    </div>
                    
                    <div class="col-span-7">
                        <div class="bg-white/10 backdrop-blur-3xl p-4 rounded-3xl border border-white/20 shadow-2xl">
                            <div class="bg-white rounded-2xl p-6 shadow-inner space-y-6">
                                <div class="flex items-center gap-4 border-b border-slate-100 pb-4">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center">🛍️</div>
                                    <div>
                                        <p class="font-bold text-slate-800">Trx #2026-004</p>
                                        <p class="text-xs text-slate-500">Selesai 2 menit yang lalu</p>
                                    </div>
                                    <div class="ml-auto font-black text-slate-900">Rp 450.000</div>
                                </div>
                                <div class="flex items-center gap-4 border-b border-slate-100 pb-4">
                                    <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center">🍱</div>
                                    <div>
                                        <p class="font-bold text-slate-800">Trx #2026-003</p>
                                        <p class="text-xs text-slate-500">Selesai 10 menit yang lalu</p>
                                    </div>
                                    <div class="ml-auto font-black text-slate-900">Rp 125.000</div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center">☕</div>
                                    <div>
                                        <p class="font-bold text-slate-800">Trx #2026-002</p>
                                        <p class="text-xs text-slate-500">Selesai 1 jam yang lalu</p>
                                    </div>
                                    <div class="ml-auto font-black text-slate-900">Rp 35.000</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="bg-slate-900 text-white py-24 text-center">
        <h3 class="text-2xl font-bold mb-4">Siap untuk bertransformasi?</h3>
        <p class="text-slate-400 mb-12">Klik style mana saja di atas untuk menjadikannya dasar desain sistem Anda.</p>
        <div class="flex justify-center gap-4">
             <a href="{{ route('dashboard') }}" class="px-8 py-3 bg-white text-slate-900 rounded-xl font-bold">KEMBALI KE DASHBOARD</a>
        </div>
    </footer>

</body>
</html>
