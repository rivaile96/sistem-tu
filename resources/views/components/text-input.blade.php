@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-slate-200 bg-slate-50 focus:bg-white focus:border-[#178BC1] focus:ring-2 focus:ring-[#178BC1]/20 rounded-xl text-xs font-medium text-slate-800 shadow-sm transition-all outline-none px-3.5 py-2.5']) }}>
