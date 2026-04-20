@extends('layouts.app')

@section('title', 'Atur Lokasi Manager')
@section('page-title', 'Atur Lokasi Manager')

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('manager-locations.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-brand transition-colors uppercase tracking-widest mb-6 px-4">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>

        <x-ui.card icon="map-marker" title="Hak Akses Lokasi">
            <div class="mb-8 p-6 bg-slate-50 rounded-3xl border border-slate-100 flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-brand/10 flex items-center justify-center text-brand text-2xl font-black">
                    {{ substr($manager->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900 leading-tight">{{ $manager->name }}</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 italic">{{ $manager->email }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('manager-locations.update', $manager) }}" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">Pilih Lokasi yang Diizinkan</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($locations as $location)
                            <label class="relative group cursor-pointer">
                                <input type="checkbox" name="location_ids[]" value="{{ $location->id }}" class="peer sr-only" {{ in_array($location->id, $selected, true) ? 'checked' : '' }}>
                                <div class="p-6 bg-white border border-slate-100 rounded-[2rem] shadow-sm transition-all group-hover:border-brand/20 peer-checked:border-brand peer-checked:bg-brand/5 peer-checked:ring-4 peer-checked:ring-brand/10">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:text-brand transition-colors peer-checked:bg-white peer-checked:text-brand">
                                                <i class="fa fa-building"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900">{{ $location->name }}</p>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-0.5">ID: {{ $location->id }}</p>
                                            </div>
                                        </div>
                                        <div class="w-6 h-6 rounded-full border-2 border-slate-100 flex items-center justify-center transition-all peer-checked:border-brand peer-checked:bg-brand text-[10px] text-white">
                                            <i class="fa fa-check"></i>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full p-12 text-center border-2 border-dashed border-slate-100 rounded-[3rem]">
                                <i class="fa fa-info-circle text-slate-200 text-4xl mb-4"></i>
                                <p class="text-slate-400 font-bold">Belum ada lokasi aktif yang terdaftar.</p>
                            </div>
                        @endforelse
                    </div>
                    @error('location_ids') <p class="text-[10px] text-rose-500 font-bold mt-2 ml-1 uppercase">{{ $message }}</p> @enderror
                </div>

                <div class="pt-8 border-t border-slate-50 flex items-center justify-end gap-3">
                    <a href="{{ route('manager-locations.index') }}" class="px-8 py-4 bg-slate-50 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-brand/20 hover:bg-brand-dark transition-all">
                        Simpan Akses Lokasi
                    </button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection
