@extends('layouts.app')

@section('title', 'Tambah Lokasi Baru')
@section('page-title', 'Tambah Lokasi')

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('locations.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-6 px-4">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>

        <x-ui.card icon="map-marker" title="Informasi Lokasi / Cabang Baru">
            <form method="POST" action="{{ route('locations.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Kode Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: HO, WH, PKU01" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('code') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Nama Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Kantor Pusat, Gudang Utama" required
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('name') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="phone" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Nomor Telepon</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxx"
                               class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">
                        @error('phone') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col gap-4">
                         <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Status & Tipe</label>
                            <div class="flex flex-wrap gap-6 mt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand"></div>
                                    <span class="ml-3 text-xs font-bold text-slate-600 uppercase tracking-widest">Aktif</span>
                                </label>

                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="toko_pusat" value="1" class="sr-only peer" {{ old('toko_pusat') ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ml-3 text-xs font-bold text-slate-600 uppercase tracking-widest">Toko Pusat</span>
                                </label>
                            </div>
                            @error('toko_pusat') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="address" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Alamat Lengkap</label>
                    <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lokasi..."
                              class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all">{{ old('address') }}</textarea>
                    @error('address') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                </div>

                <div class="pt-6 border-t border-slate-50 flex items-center justify-end gap-3">
                    <a href="{{ route('locations.index') }}" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        Simpan Lokasi
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
