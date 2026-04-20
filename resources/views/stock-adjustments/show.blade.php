@extends('layouts.app')

@section('title', __('adjustment.detail'))
@section('page-title', __('adjustment.page_title'))

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 pb-12">
        {{-- Navigation Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('stock-adjustments.index') }}" 
                   class="w-10 h-10 flex items-center justify-center bg-white rounded-xl text-slate-400 hover:text-brand hover:shadow-md transition-all border border-slate-100">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">{{ __('adjustment.detail') }}</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Reference: {{ $stockAdjustment->reference_no }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @if($stockAdjustment->status === 'pending')
                    <form method="POST" action="{{ route('stock-adjustments.approve', $stockAdjustment) }}">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('adjustment.approve_confirm') }}')" 
                                class="px-6 py-2.5 bg-brand text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-brand/20 hover:scale-105 transition-all">
                            <i class="fa fa-check-circle mr-2"></i>{{ __('adjustment.approve_btn') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('stock-adjustments.destroy', $stockAdjustment) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('{{ __('adjustment.delete_confirm') }}')" 
                                class="px-6 py-2.5 bg-white text-slate-400 border border-slate-100 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                            <i class="fa fa-trash mr-2"></i>{{ __('adjustment.batal') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left column: Core Data --}}
            <div class="lg:col-span-2 space-y-8">
                <x-ui.card class="overflow-hidden">
                    <div class="p-2 -m-8 mb-8 bg-slate-50/50 border-b border-slate-100 px-8 py-6 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-slate-400 shadow-sm border border-slate-100/50">
                                <i class="fa fa-cube fa-xl text-brand"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-800 leading-tight">{{ $stockAdjustment->product?->name }}</h3>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-tighter mt-1 bg-slate-100 px-2 py-0.5 rounded-lg inline-block">SKU: {{ $stockAdjustment->product?->sku }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                             <span class="px-4 py-1.5 {{ $stockAdjustment->status === 'approved' ? 'bg-emerald-500 shadow-emerald-500/20' : 'bg-amber-500 shadow-amber-500/20' }} text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg">
                                {{ $stockAdjustment->status }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">{{ __('adjustment.location') }}</label>
                                <div class="p-4 bg-indigo-50/30 rounded-2xl border border-indigo-100/50 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-500">
                                        <i class="fa fa-map-marker"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-700 uppercase tracking-tight">{{ $stockAdjustment->location?->name }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">{{ __('adjustment.qty_delta') }}</label>
                                @php $delta = (float) $stockAdjustment->quantity_delta; @endphp
                                <div class="relative overflow-hidden p-6 rounded-2xl border {{ $delta > 0 ? 'bg-emerald-50/50 border-emerald-100' : 'bg-rose-50/50 border-rose-100' }}">
                                    <div class="absolute -right-4 -bottom-4 opacity-5 {{ $delta > 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                        <i class="fa {{ $delta > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-7xl"></i>
                                    </div>
                                    <div class="relative flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl {{ $delta > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center shadow-sm">
                                            <i class="fa {{ $delta > 0 ? 'fa-plus' : 'fa-minus' }} fa-lg"></i>
                                        </div>
                                        <div>
                                            <span class="text-3xl font-black {{ $delta > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }}
                                            </span>
                                            <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest mt-1">{{ $delta > 0 ? 'Penambahan Stok' : 'Pengurangan Stok' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">{{ __('adjustment.reason') }}</label>
                                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 relative min-h-[140px]">
                                    <i class="fa fa-quote-left absolute top-4 left-4 text-slate-200"></i>
                                    <p class="text-sm font-medium text-slate-500 leading-relaxed italic pl-4">
                                        "{{ $stockAdjustment->reason ?: 'Tidak ada alasan yang diberikan' }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                @if($stockAdjustment->evidence_path)
                    <x-ui.card icon="camera" title="{{ __('adjustment.evidence') }}">
                        <div class="group relative overflow-hidden rounded-3xl border border-slate-100 shadow-sm transition-all hover:shadow-xl">
                            <img src="{{ Storage::url($stockAdjustment->evidence_path) }}" alt="Evidence" class="w-full h-auto object-cover max-h-[600px]">
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all backdrop-blur-sm">
                                <a href="{{ Storage::url($stockAdjustment->evidence_path) }}" target="_blank" 
                                   class="px-8 py-3 bg-white text-slate-900 rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl hover:scale-110 transition-all">
                                    <i class="fa fa-external-link mr-2"></i> {{ __('adjustment.detail') }}
                                </a>
                            </div>
                        </div>
                    </x-ui.card>
                @endif
            </div>

            {{-- Right column: Meta Info --}}
            <div class="space-y-8">
                <x-ui.card icon="history" title="Log & PIC">
                    <div class="space-y-6">
                        {{-- Requested By --}}
                        <div class="relative pl-6 border-l-2 border-slate-50">
                            <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-300"></div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block mb-2">PENGASUAN (REQUESTED)</label>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">{{ $stockAdjustment->requester?->name }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-tighter italic">{{ $stockAdjustment->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Approved By (if exists) --}}
                        @if($stockAdjustment->approved_at)
                            <div class="relative pl-6 border-l-2 border-emerald-50">
                                <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/40"></div>
                                <label class="text-[9px] font-black text-emerald-500 uppercase tracking-widest block mb-2">PERSETUJUAN (APPROVED)</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 text-xs shadow-sm">
                                        <i class="fa fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">{{ $stockAdjustment->approver?->name }}</p>
                                        <p class="text-[10px] font-bold text-emerald-500 mt-0.5 tracking-tighter italic">{{ $stockAdjustment->approved_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @else
                             <div class="relative pl-6 border-l-2 border-amber-50">
                                <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-amber-400"></div>
                                <label class="text-[9px] font-black text-amber-500 uppercase tracking-widest block mb-2">STATUS</label>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 text-xs">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <p class="text-xs font-black text-amber-600 uppercase tracking-widest">Menunggu Persetujuan</p>
                                </div>
                            </div>
                        @endif

                        <div class="pt-4 border-t border-slate-50 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">WAKTU TUNGGU</span>
                                <span class="text-[10px] font-bold text-slate-600">
                                    {{ $stockAdjustment->created_at->diffForHumans($stockAdjustment->approved_at ?: now(), true) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ID TRANSAKSI</span>
                                <span class="text-[10px] font-black text-slate-800 bg-slate-50 px-2 py-0.5 rounded tracking-tighter">{{ $stockAdjustment->id }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Action Card for Pending --}}
                @if($stockAdjustment->status === 'pending')
                    <div class="p-6 bg-brand/5 border border-brand/10 rounded-[2rem] space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-brand text-white flex items-center justify-center shadow-lg shadow-brand/20">
                                <i class="fa fa-bolt"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 tracking-tight">Butuh Persetujuan</h4>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Data koreksi ini harus diverifikasi oleh Owner atau Manager sebelum stok barang diperbarui secara resmi di sistem.
                        </p>
                        <form method="POST" action="{{ route('stock-adjustments.approve', $stockAdjustment) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('{{ __('adjustment.approve_confirm') }}')" 
                                    class="w-full py-3 bg-brand text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-brand/30 hover:bg-brand-dark transition-all">
                                Update Stok Sekarang
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
