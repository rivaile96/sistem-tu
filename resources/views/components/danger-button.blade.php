<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-rose-500/30 active:scale-[0.98] disabled:opacity-50']) }}>
    {{ $slot }}
</button>
