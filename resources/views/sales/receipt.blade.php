@extends('layouts.app')

@section('title', 'Sale Receipt')

@section('content')
    <style>
        /* receipt-view-isolation */
        header, .fixed.inset-y-0, footer { display: none !important; }
        .lg\:pl-72 { padding-left: 0 !important; }
        main.flex-1 { padding: 0 !important; }
        body { background-color: #f1f5f9 !important; }

        @media print {
            @page {
                margin: 0;
                size: auto;
            }
            html, body { 
                background-color: white !important; 
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }
            .print-hidden { display: none !important; }
            
            /* Reset wrapper constraints - Remove flex centering for print */
            .min-h-screen { 
                min-height: 0 !important; 
                height: auto !important; 
                display: block !important; 
                padding: 0 !important;
                margin: 0 !important;
            }
            .py-12, .px-4 { padding: 0 !important; }
            
            #receipt-area {
                width: 76mm !important;
                max-width: 76mm !important;
                margin: 0 auto !important;
                padding: 5mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                overflow: visible !important;
                position: relative !important;
                top: 0 !important;
                left: 0 !important;
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid-page !important;
            }

            /* Tighten spacings for print */
            #receipt-area .mb-12 { margin-bottom: 0.75rem !important; }
            #receipt-area .mb-10 { margin-bottom: 0.5rem !important; }
            #receipt-area .mt-10 { margin-top: 0.5rem !important; }
            #receipt-area .mt-16 { margin-top: 0.75rem !important; }
            #receipt-area .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.5rem !important; }
            #receipt-area .space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.25rem !important; }
            #receipt-area .p-8, #receipt-area .p-12, #receipt-area .sm\:p-12 { padding: 0 !important; }
            #receipt-area .p-6 { padding: 0.5rem !important; }
            #receipt-area .pt-8 { padding-top: 0.5rem !important; }
            #receipt-area .pt-4 { padding-top: 0.25rem !important; }
            #receipt-area .py-6 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
            #receipt-area .mt-4 { margin-top: 0.25rem !important; }
            
            /* Adjust font sizes if needed */
            #receipt-area .text-3xl { font-size: 1.25rem !important; }
            #receipt-area .text-2xl { font-size: 1.1rem !important; }
        }
    </style>

    <div class="flex flex-col items-center justify-center min-h-screen py-12 px-4 text-slate-900">
        
        <!-- Receipt Content -->
        <div id="receipt-area" class="w-full max-w-[400px] bg-white p-8 sm:p-12 shadow-[0_25px_100px_-15px_rgba(0,0,0,0.1)] rounded-[3rem] border border-white relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute top-0 left-0 w-full h-2 bg-brand/5"></div>
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand/5 rounded-full blur-3xl"></div>
            
            <div class="text-center space-y-4 mb-12">
                <div class="w-20 h-20 bg-slate-900 rounded-[2.5rem] flex items-center justify-center text-white mx-auto shadow-2xl shadow-slate-200">
                    <i class="fa fa-shopping-basket text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter italic">{{ $sale->location?->name ?? config('app.name') }}</h3>
                    <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest mt-1.5 leading-relaxed">{{ $sale->location?->address ?? 'Operation Address' }}</p>
                    <p class="text-[10px] font-bold text-slate-300 mt-1 italic">Phone: {{ $sale->location?->phone ?? '-' }}</p>
                </div>
            </div>

            <!-- Transaction Detail Barcode/ID -->
            <div class="bg-slate-50 rounded-[2rem] p-6 mb-10 space-y-4 border border-slate-100">
                <div class="flex justify-between items-center">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Order Ref</span>
                    <span class="text-xs font-black text-slate-900 tracking-tighter italic">#{{ $sale->reference_no }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-slate-400 uppercase tracking-widest">Time</span>
                    <span class="text-slate-700">{{ $sale->posted_at?->format('d/m/Y H:i') ?? $sale->created_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between items-center text-[10px] font-bold">
                    <span class="text-slate-400 uppercase tracking-widest">Cashier</span>
                    <span class="text-slate-900 uppercase tracking-tighter">{{ $sale->cashier?->name ?? 'Cashier' }}</span>
                </div>
            </div>

            <!-- Items Section -->
            <div class="space-y-8 mb-12">
                <div class="flex justify-between text-[9px] font-black text-slate-300 uppercase tracking-[0.3em] pb-3 border-b border-slate-50 italic">
                    <span class="w-1/2">Item</span>
                    <span class="w-1/6 text-center">Qty</span>
                    <span class="w-1/3 text-right">Total</span>
                </div>

                @foreach ($sale->items as $item)
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <p class="text-xs font-black text-slate-900 uppercase leading-snug tracking-tight">{{ $item->product?->name ?? '-' }}</p>
                            <p class="text-[9px] text-slate-400 font-bold mt-1 tracking-wider italic">@ Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-10 text-center text-xs font-black text-slate-800 tabular-nums">
                            {{ number_format((float) $item->quantity, 0, ',', '.') }}
                        </div>
                        <div class="w-24 text-right text-xs font-black text-slate-900 tabular-nums italic">
                            {{ number_format((float) $item->line_total, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="pt-8 border-t-2 border-dashed border-slate-100 space-y-4">
                <div class="flex justify-between items-center text-[11px] font-bold text-slate-400">
                    <span class="uppercase tracking-widest">Subtotal</span>
                    <span class="tabular-nums font-black text-slate-600 italic">Rp {{ number_format((float) $sale->subtotal, 0, ',', '.') }}</span>
                </div>
                
                @if($sale->order_discount > 0)
                    <div class="flex justify-between items-center text-[11px] font-bold text-rose-500">
                        <span class="uppercase tracking-widest italic">Discount</span>
                        <span class="tabular-nums font-black"> - Rp {{ number_format((float) $sale->order_discount, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="flex justify-between items-end pt-6">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em] mb-1 italic">Net Total</span>
                        <span class="text-3xl font-black text-slate-900 tracking-tighter italic">Rp {{ number_format((float) $sale->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="mt-10 p-6 bg-slate-900 rounded-[2.5rem] text-white">
                @foreach ($sale->payments as $p)
                    <div class="flex justify-between items-center text-[10px] mb-3 last:mb-0">
                        <span class="font-black opacity-50 uppercase tracking-[0.2em] italic">{{ $p->method }}</span>
                        <span class="font-bold tabular-nums">Rp {{ number_format((float) $p->amount, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                
                <div class="mt-4 pt-4 border-t border-white/10 flex justify-between items-center">
                    <span class="text-[9px] font-black text-brand uppercase tracking-[0.3em]">Change Due</span>
                    <span class="text-xl font-black text-brand tracking-tighter tabular-nums italic">Rp {{ number_format((float) $sale->change_due, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Receipt Footer -->
            <div class="mt-16 text-center">
                <div class="py-6 border-y border-slate-50 relative">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 bg-white text-[9px] font-black text-slate-300 uppercase tracking-[0.3em] italic">Closing Tag</div>
                    <p class="text-sm font-black text-slate-900 uppercase tracking-[0.2em] italic">Thank You</p>
                    <p class="text-[10px] font-bold text-slate-400 mt-1 italic">Please Come Again</p>
                </div>
                <div class="mt-10 flex justify-center items-center gap-2 opacity-30">
                     <span class="w-8 h-px bg-slate-300"></span>
                     <p class="text-[9px] font-black uppercase tracking-[0.5em] text-slate-400 italic">{{ config('app.name') }} POS</p>
                     <span class="w-8 h-px bg-slate-300"></span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4 mt-12 print-hidden relative z-50">
            <a href="{{ route('sales.index') }}" class="px-10 py-5 bg-white text-slate-900 rounded-3xl text-[11px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all border border-slate-200">
                <i class="fa fa-arrow-left mr-2 font-light"></i> POS Menu
            </a>
            <button type="button" onclick="window.print()" class="px-14 py-5 bg-slate-900 text-white rounded-3xl text-[11px] font-black uppercase tracking-widest shadow-2xl shadow-slate-900/40 hover:scale-105 transition-all">
                <i class="fa fa-print mr-2"></i> Print Receipt
            </button>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
@endsection
