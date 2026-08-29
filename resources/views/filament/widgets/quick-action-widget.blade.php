<x-filament-widgets::widget>
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:1rem; padding:1rem 1.25rem; box-shadow:0 2px 4px rgba(0,0,0,0.04);" class="dark:!bg-slate-900 dark:!border-slate-800">
        <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
            <!-- Judul Aksi Cepat & Kuota Cuti -->
            <div style="display:flex; align-items:center; gap:0.65rem;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:0.5rem; background-color:#ecfdf5; color:#436354; font-size:1rem; font-weight:bold;">
                    ⚡
                </span>
                <div>
                    <span style="font-size:0.875rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:#1e293b;" class="dark:!text-slate-100">
                        Aksi Cepat
                    </span>
                    @if($user && $isSecurity)
                        <div style="margin-top:0.15rem;">
                            <span style="display:inline-flex; align-items:center; gap:0.35rem; border-radius:9999px; background-color:#f1f5f9; padding:0.2rem 0.65rem; font-size:0.75rem; font-weight:600; color:#475569;" class="dark:!bg-slate-800 dark:!text-slate-300">
                                Sisa Cuti: <strong style="color:#436354;" class="dark:!text-emerald-400">{{ $user->remaining_leave_quota }} Hari</strong>
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tombol Pintasan Lebih Besar & Nyaman Diklik -->
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem;">
                <!-- 1. Presensi Harian -->
                <a
                    href="{{ url('/internal/security-attendances/create') }}"
                    style="display:inline-flex; align-items:center; gap:0.5rem; border-radius:0.75rem; background-color:#436354; padding:0.65rem 1.25rem; font-size:0.875rem; font-weight:700; color:#ffffff; text-decoration:none; box-shadow:0 4px 6px -1px rgba(67, 99, 84, 0.25); transition:all 0.15s ease;"
                    onmouseover="this.style.backgroundColor='#344E42'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.backgroundColor='#436354'; this.style.transform='translateY(0)';"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px; height:18px; min-width:18px; min-height:18px; display:inline-block; flex-shrink:0;">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                    <span>Presensi Harian</span>
                </a>

                <!-- 2. Pengajuan Cuti / Izin / Sakit -->
                <a
                    href="{{ url('/internal/leave-requests/create') }}"
                    style="display:inline-flex; align-items:center; gap:0.5rem; border-radius:0.75rem; border:1.5px solid #cbd5e1; background-color:#ffffff; padding:0.65rem 1.25rem; font-size:0.875rem; font-weight:700; color:#334155; text-decoration:none; box-shadow:0 2px 4px rgba(0,0,0,0.04); transition:all 0.15s ease;"
                    class="dark:!bg-slate-800 dark:!border-slate-700 dark:!text-slate-200"
                    onmouseover="this.style.borderColor='#436354'; this.style.backgroundColor='#F0F5F2'; this.style.color='#1B2923'; this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#ffffff'; this.style.color='#334155'; this.style.transform='translateY(0)';"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#436354" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="width:18px; height:18px; min-width:18px; min-height:18px; display:inline-block; flex-shrink:0;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Ajukan Cuti / Izin / Sakit</span>
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
