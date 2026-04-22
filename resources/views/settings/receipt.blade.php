@extends('layouts.app')

@section('title', __('settings.receipt_title'))
@section('page-title', __('settings.receipt_title'))

@section('content')
    <div class="pb-20">
        <div class="flex flex-col lg:flex-row gap-8 lg:items-start">
            <!-- Left: Config Inputs -->
            <div class="w-full lg:w-3/4 xl:w-4/5">
                <x-ui.card icon="cog" title="{{ __('settings.receipt_title') }}">
                    <x-slot name="actions">
                        <span class="text-[10px] font-black text-brand uppercase tracking-widest bg-brand/10 px-4 py-1.5 rounded-xl border border-brand/20">
                            {{ $location->name }}
                        </span>
                    </x-slot>

                    <div class="mb-10">
                        <p class="text-sm text-slate-500 font-medium leading-relaxed italic">{{ __('settings.receipt_desc') }}</p>
                    </div>

                    <form action="{{ route('settings.receipt.update') }}" method="POST" id="receipt-form" class="space-y-10">
                        @csrf
                        @method('PUT')

                        <div class="space-y-8">
                            <!-- Receipt Header -->
                            <div class="group space-y-3">
                                <div class="flex items-center gap-2 mb-1 ml-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-brand"></div>
                                    <label for="receipt_header" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                                        {{ __('settings.receipt_header') }}
                                    </label>
                                </div>
                                <textarea id="receipt_header" name="receipt_header" rows="3" 
                                          placeholder="{{ __('settings.receipt_header_placeholder') }}"
                                          class="w-full bg-slate-50 border-transparent rounded-[2.5rem] px-10 py-6 text-sm font-bold text-slate-700 focus:bg-white focus:ring-[12px] focus:ring-brand/5 focus:border-brand/20 transition-all duration-300 outline-none">{{ old('receipt_header', $location->receipt_header) }}</textarea>
                                @error('receipt_header') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-4">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Receipt Footer -->
                                <div class="group space-y-3">
                                    <div class="flex items-center gap-2 mb-1 ml-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                        <label for="receipt_footer" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                                            {{ __('settings.receipt_footer') }}
                                        </label>
                                    </div>
                                    <textarea id="receipt_footer" name="receipt_footer" rows="4" 
                                              placeholder="{{ __('settings.receipt_footer_placeholder') }}"
                                              class="w-full bg-slate-50 border-transparent rounded-[2.5rem] px-10 py-6 text-sm font-bold text-slate-700 focus:bg-white focus:ring-[12px] focus:ring-indigo-500/5 focus:border-indigo-500/20 transition-all duration-300 outline-none">{{ old('receipt_footer', $location->receipt_footer) }}</textarea>
                                    @error('receipt_footer') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-4">{{ $message }}</p> @enderror
                                </div>

                                <!-- Receipt Tagline -->
                                <div class="group space-y-3">
                                    <div class="flex items-center gap-2 mb-1 ml-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500"></div>
                                        <label for="receipt_tagline" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">
                                            {{ __('settings.receipt_tagline') }}
                                        </label>
                                    </div>
                                    <div class="relative">
                                        <input type="text" id="receipt_tagline" name="receipt_tagline" 
                                               placeholder="{{ __('settings.receipt_tagline_placeholder') }}"
                                               value="{{ old('receipt_tagline', $location->receipt_tagline) }}"
                                               class="w-full bg-slate-50 border-transparent rounded-[2.5rem] px-10 py-6 text-sm font-bold text-slate-700 focus:bg-white focus:ring-[12px] focus:ring-amber-500/5 focus:border-amber-500/20 transition-all duration-300 outline-none">
                                        <div class="absolute right-8 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-amber-500 transition-colors">
                                            <i class="fa fa-quote-right text-xs"></i>
                                        </div>
                                    </div>
                                    @error('receipt_tagline') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-4">{{ $message }}</p> @enderror
                                    
                                    <div class="p-6 bg-slate-50 rounded-3xl mt-4 border border-dashed border-slate-200">
                                        <p class="text-[10px] text-slate-400 font-bold leading-relaxed italic">
                                            <i class="fa fa-info-circle mr-1"></i> Tip: Use bold or capitalized words for the tagline to make it pop at the bottom of the receipt.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-10 flex items-center justify-between border-t border-slate-100">
                            <a href="{{ route('dashboard') }}" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="fa fa-arrow-left mr-2"></i> Back to Dashboard
                            </a>
                            <div class="flex gap-4">
                                <a href="{{ route('settings.receipt.edit') }}" class="px-10 py-5 bg-slate-100 text-slate-500 rounded-[2rem] font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">
                                    {{ __('app.cancel') }}
                                </a>
                                <button type="submit" class="px-14 py-5 bg-brand text-white rounded-[2rem] font-black text-[10px] uppercase tracking-widest shadow-[0_20px_50px_-10px_rgba(var(--color-brand),0.4)] hover:bg-brand-dark hover:scale-[1.02] active:scale-95 transition-all">
                                    {{ __('app.save_changes') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <!-- Right: Live Preview -->
            <div class="w-full lg:w-1/4 xl:w-1/5">
                <div class="sticky top-24 space-y-6">
                    <div class="flex items-center justify-between px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-brand/10 flex items-center justify-center text-brand">
                                <i class="fa fa-eye text-xs"></i>
                            </div>
                            <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400">Live Preview</h4>
                        </div>
                        <div class="flex gap-1">
                            <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse delay-75"></span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse delay-150"></span>
                        </div>
                    </div>

                    <!-- Mock Receipt Container -->
                    <div class="relative group">
                        <!-- Shadow Glow -->
                        <div class="absolute -inset-4 bg-gradient-to-tr from-brand/10 to-indigo-500/10 rounded-[4rem] blur-2xl opacity-50 group-hover:opacity-100 transition-opacity duration-700"></div>
                        
                        <div class="relative bg-white p-6 shadow-2xl shadow-slate-200/50 rounded-[2.5rem] border border-slate-50 overflow-hidden ring-1 ring-slate-100/50 max-w-[260px] mx-auto">
                            <!-- Top Edge Detail -->
                            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-brand to-indigo-500 opacity-20"></div>
                            
                            <!-- Header Content -->
                            <div class="text-center space-y-4 mb-10">
                                <div class="w-16 h-16 bg-slate-900 rounded-3xl flex items-center justify-center text-white mx-auto shadow-2xl shadow-slate-200 ring-4 ring-white">
                                    <i class="fa fa-shopping-basket text-xl"></i>
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xl font-black text-slate-900 tracking-tighter italic leading-none">{{ $location->name }}</h3>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $location->address ?? 'Operation Address' }}</p>
                                    <div class="flex items-center justify-center gap-2 text-[8px] font-bold text-slate-300 italic">
                                        <span>Phone: {{ $location->phone ?? '-' }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                        <span>ID: #1002</span>
                                    </div>
                                    
                                    <!-- Dynamic Header -->
                                    <div id="preview-header" class="pt-3 px-4 text-[9px] font-medium text-slate-400 leading-relaxed italic whitespace-pre-line border-t border-dashed border-slate-100 mt-3 hidden">
                                        {{ $location->receipt_header }}
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Info -->
                            <div class="bg-slate-50 rounded-2xl p-5 mb-10 space-y-3 border border-slate-100/50">
                                <div class="flex justify-between items-center text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                    <span>Receipt No</span>
                                    <span class="text-slate-900 italic">#INV/26/0422/001</span>
                                </div>
                                <div class="flex justify-between items-center text-[9px] font-bold text-slate-400">
                                    <span>Date & Time</span>
                                    <span class="text-slate-700">{{ now()->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>

                            <!-- Items Section -->
                            <div class="space-y-4 mb-8">
                                <div class="flex justify-between items-start group/item">
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Executive Soft Product</p>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 tracking-wider italic">@ Rp 125.000</p>
                                    </div>
                                    <div class="w-16 text-right text-xs font-black text-slate-900 italic tabular-nums">125.000</div>
                                </div>

                                <div class="flex justify-between items-start group/item">
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Premium Service Add-on</p>
                                        <p class="text-[9px] text-slate-400 font-bold mt-1 tracking-wider italic">@ Rp 25.000</p>
                                    </div>
                                    <div class="w-16 text-right text-xs font-black text-slate-900 italic tabular-nums">25.000</div>
                                </div>
                                
                                <div class="pt-6 border-t-2 border-dashed border-slate-50 space-y-3">
                                    <div class="flex justify-between items-end">
                                        <div class="flex flex-col">
                                            <span class="text-[7px] font-black text-slate-300 uppercase tracking-[0.4em] mb-1 italic">Total Amount</span>
                                            <span class="text-2xl font-black text-slate-900 tracking-tighter italic">Rp 150.000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Section -->
                            <div class="p-6 bg-slate-900 rounded-[2.5rem] text-white mb-10 shadow-xl shadow-slate-200">
                                <div class="flex justify-between items-center text-[10px] mb-4 opacity-50 font-black uppercase tracking-[0.2em] italic">
                                    <span>Paid via Cash</span>
                                    <span class="text-white opacity-100">Rp 150.000</span>
                                </div>
                                <div class="pt-4 border-t border-white/10 flex justify-between items-center">
                                    <span class="text-[9px] font-black text-brand uppercase tracking-[0.3em]">Change Due</span>
                                    <span class="text-xl font-black text-brand tracking-tighter tabular-nums italic">Rp 25.000</span>
                                </div>
                            </div>

                            <!-- Dynamic Footer -->
                            <div class="text-center py-8 border-y border-slate-50 relative">
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 bg-white text-[8px] font-black text-slate-200 uppercase tracking-[0.5em] italic">Closing</div>
                                <p id="preview-footer" class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] italic leading-tight">
                                    {{ $location->receipt_footer ?? 'Thank You' }}
                                </p>
                                <p id="preview-tagline" class="text-[10px] font-bold text-slate-400 mt-2 italic">
                                    {{ $location->receipt_tagline ?? 'Please Come Again' }}
                                </p>
                            </div>

                            <!-- Brand Footer -->
                            <div class="mt-10 flex justify-center items-center gap-3 opacity-30">
                                 <span class="w-6 h-px bg-slate-300"></span>
                                 <p class="text-[8px] font-black uppercase tracking-[0.6em] text-slate-400 italic">{{ config('app.name') }} POS</p>
                                 <span class="w-6 h-px bg-slate-300"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const headerInput = document.getElementById('receipt_header');
        const footerInput = document.getElementById('receipt_footer');
        const taglineInput = document.getElementById('receipt_tagline');

        const headerPreview = document.getElementById('preview-header');
        const footerPreview = document.getElementById('preview-footer');
        const taglinePreview = document.getElementById('preview-tagline');

        function updatePreview() {
            // Header
            const headerVal = headerInput.value.trim();
            if (headerVal) {
                headerPreview.textContent = headerVal;
                headerPreview.classList.remove('hidden');
                headerPreview.parentElement.classList.add('space-y-4'); // Add spacing when header is visible
            } else {
                headerPreview.classList.add('hidden');
            }

            // Footer
            footerPreview.textContent = footerInput.value.trim() ? footerInput.value : 'Thank You';

            // Tagline
            taglinePreview.textContent = taglineInput.value.trim() ? taglineInput.value : 'Please Come Again';
        }

        // Listen for input and change events
        ['input', 'change', 'keyup'].forEach(evt => {
            headerInput.addEventListener(evt, updatePreview);
            footerInput.addEventListener(evt, updatePreview);
            taglineInput.addEventListener(evt, updatePreview);
        });

        // Initial call
        updatePreview();
    });
</script>
@endpush
