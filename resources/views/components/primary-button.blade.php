<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-[#178BC1] hover:bg-[#126da3] border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider shadow-sm hover:shadow transition-all focus:outline-none focus:ring-2 focus:ring-[#178BC1]/30 active:scale-[0.98] disabled:opacity-50']) }}>
    {{ $slot }}
</button>
