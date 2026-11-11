<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-xs text-slate-700 uppercase tracking-wider shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 disabled:opacity-50 transition-all']) }}>
    {{ $slot }}
</button>
