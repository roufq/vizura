@extends('layouts.app')

@section('title', __('transfer.create_new_title'))
@section('page-title', __('transfer.page_title'))

@section('content')
    <div class="space-y-8">
        <a href="{{ route('stock-transfers.index') }}" class="group inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 hover:text-brand transition-all">
            <i class="fa fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
            {{ __('app.back_to_list') }}
        </a>

        <form method="POST" action="{{ route('stock-transfers.store') }}" id="transfer-form" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Main Info -->
                <div class="lg:col-span-12">
                    <x-ui.card icon="truck" title="{{ __('transfer.route_info') }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <label for="reference_no" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('transfer.doc_no') }} <span class="text-rose-500">*</span></label>
                                <input type="text" id="reference_no" name="reference_no" value="{{ old('reference_no', $referenceNo) }}" readonly
                                       class="w-full bg-slate-100 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-500 cursor-not-allowed">
                                @error('reference_no') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="source_location_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('transfer.source_loc') }} <span class="text-rose-500">*</span></label>
                                <select id="source_location_id" name="source_location_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all cursor-not-allowed" disabled>
                                    @if ($sourceLocation)
                                        <option value="{{ $sourceLocation->id }}" selected>{{ $sourceLocation->name }}</option>
                                    @endif
                                </select>
                                <input type="hidden" name="source_location_id" value="{{ $sourceLocation->id }}">
                                @error('source_location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="destination_location_id" class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-1">{{ __('transfer.dest_loc') }} <span class="text-rose-500">*</span></label>
                                <select id="destination_location_id" name="destination_location_id" class="w-full bg-slate-50 border-transparent rounded-2xl px-6 py-4 text-sm font-bold text-slate-800 focus:ring-4 focus:ring-brand/10 transition-all" required>
                                    <option value="" disabled {{ old('destination_location_id') ? '' : 'selected' }}>{{ __('transfer.select_dest') }}</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" {{ (int) old('destination_location_id') === $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('destination_location_id') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </x-ui.card>
                </div>

                <!-- Products Table -->
                <div class="lg:col-span-12">
                    <x-ui.card icon="box" title="{{ __('transfer.item_detail') }}">
                        <div class="overflow-x-auto -mx-8">
                            <table class="w-full text-left">
                                <thead class="bg-slate-50 border-y border-slate-100">
                                    <tr>
                                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ __('transfer.select_product') }}</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center" style="width: 200px;">{{ __('transfer.qty') }}</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center" style="width: 80px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="transfer-items" class="divide-y divide-slate-50 font-bold">
                                    @php $items = old('items', [ [] ]); @endphp
                                    @foreach ($items as $index => $item)
                                        <tr data-index="{{ $index }}" class="group hover:bg-slate-50/50 transition-all">
                                            <td class="px-8 py-4">
                                                <select name="items[{{ $index }}][product_id]" class="w-full bg-transparent border-none text-sm font-bold text-slate-900 focus:ring-0 js__select2" required>
                                                    <option value="">{{ __('transfer.search_product') }}</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}" {{ (int) ($item['product_id'] ?? 0) === $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }} ({{ $product->sku }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="number" step="0.01" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" placeholder="0.00" required
                                                       class="w-full bg-slate-50 border-transparent rounded-xl px-4 py-3 text-center text-sm font-black text-brand focus:ring-4 focus:ring-brand/10 transition-all">
                                            </td>
                                            <td class="px-8 py-4 text-center">
                                                <button type="button" class="p-2 text-slate-300 hover:text-rose-500 js-remove-row transition-colors">
                                                    <i class="fa fa-times-circle text-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex items-center justify-between">
                            <button type="button" id="add-transfer-row" class="px-6 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-slate-800 transition-all active:scale-95">
                                + {{ __('transfer.add_product') }}
                            </button>
                            <p class="text-[10px] font-bold text-slate-400 italic">{{ __('transfer.stock_check_notice', ['location' => $sourceLocation->name]) }}</p>
                        </div>
                    </x-ui.card>
                </div>

                <div class="lg:col-span-12 flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('stock-transfers.index') }}" class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-200 transition-all">
                        {{ __('transfer.cancel') }}
                    </a>
                    <button type="submit" class="px-10 py-4 bg-brand text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-brand/40 hover:bg-brand-dark transition-all">
                        {{ __('transfer.save_and_prepare') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Template for new rows -->
    <template id="transfer-item-template">
        <tr data-index="__INDEX__" class="group hover:bg-slate-50/50 transition-all">
            <td class="px-8 py-4">
                <select name="items[__INDEX__][product_id]" class="w-full bg-transparent border-none text-sm font-bold text-slate-900 focus:ring-0 js__select1" required>
                    <option value="">{{ __('transfer.search_product') }}</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                    @endforeach
                </select>
            </td>
            <td class="px-6 py-4">
                <input type="number" step="0.01" name="items[__INDEX__][quantity]" placeholder="0.00" required
                       class="w-full bg-slate-50 border-transparent rounded-xl px-4 py-3 text-center text-sm font-black text-brand focus:ring-4 focus:ring-brand/10 transition-all">
            </td>
            <td class="px-8 py-4 text-center">
                <button type="button" class="p-2 text-slate-300 hover:text-rose-500 js-remove-row transition-colors">
                    <i class="fa fa-times-circle text-lg"></i>
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
        <script src="{{ asset('assets/js/pages/stock-transfer-create.js') }}"></script>
    @endpush
@endsection
