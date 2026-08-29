<x-filament-panels::page>
    {{ $this->content }}

    {{-- Modal Popup Sukses & Ringkasan Presensi --}}
    @if ($showSuccessModal && $createdAttendance)
        @php
            $selfieSrc = $createdAttendance->selfie_data_url ?? $createdAttendance->selfie_url;
        @endphp

        <div
            x-data="{ show: @entangle('showSuccessModal') }"
            x-show="show"
            x-cloak
            style="position: fixed !important; inset: 0 !important; z-index: 99999 !important; background-color: rgba(15, 23, 42, 0.75) !important; backdrop-filter: blur(4px) !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 1rem !important;"
        >
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                style="position: relative !important; width: 100% !important; max-width: 480px !important; max-height: 92vh !important; background: #ffffff !important; border-radius: 1rem !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35) !important; overflow: hidden !important; display: flex !important; flex-direction: column !important; margin: auto !important; border: 1px solid #e2e8f0 !important;"
                x-on:click.outside="$wire.closeModalAndRedirect()"
            >
                {{-- AREA KARTU POPUP YANG AKAN DI-SCREENSHOT --}}
                <div id="attendance-popup-card" style="background-color: #ffffff !important; overflow: hidden !important; display: flex !important; flex-direction: column !important;">
                    {{-- Header Modal --}}
                    <div style="background: linear-gradient(135deg, #436354, #5F8575) !important; padding: 1rem 1.25rem !important; color: #ffffff !important; display: flex !important; align-items: center !important; gap: 0.75rem !important; flex-shrink: 0 !important;">
                        <div style="width: 2.5rem !important; height: 2.5rem !important; border-radius: 9999px !important; background-color: rgba(255, 255, 255, 0.25) !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 1.25rem !important; font-weight: bold !important; flex-shrink: 0 !important;">
                            ✓
                        </div>
                        <div style="min-width: 0 !important;">
                            <h3 style="margin: 0 !important; font-size: 1.1rem !important; font-weight: 800 !important; color: #ffffff !important; line-height: 1.3 !important;">
                                Presensi Security Amabel
                            </h3>
                            <p style="margin: 0 !important; font-size: 0.75rem !important; color: #d1e7dd !important;">
                                {{ $createdAttendance->day_name }}, {{ \Illuminate\Support\Carbon::parse($createdAttendance->attendance_date)->translatedFormat('d M Y') }} • {{ \Illuminate\Support\Carbon::parse($createdAttendance->attendance_time)->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    {{-- Body Modal (Foto + Ringkasan) --}}
                    <div style="padding: 1rem 1.25rem !important; display: flex !important; flex-direction: column !important; gap: 0.875rem !important; background-color: #ffffff !important;">
                        {{-- Foto Selfie --}}
                        @if ($selfieSrc)
                            <div style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; border-radius: 0.75rem !important; padding: 0.5rem !important; text-align: center !important;">
                                <img
                                    id="attendance-selfie-img"
                                    src="{{ $selfieSrc }}"
                                    alt="Foto Selfie Presensi"
                                    style="max-height: 160px !important; width: auto !important; max-width: 100% !important; margin: 0 auto !important; object-fit: contain !important; border-radius: 0.5rem !important; display: block !important;"
                                />
                                <p style="margin: 0.35rem 0 0 0 !important; font-size: 0.7rem !important; font-weight: 600 !important; color: #64748b !important;">
                                    📷 Foto Selfie Wajah Petugas
                                </p>
                            </div>
                        @endif

                        {{-- Ringkasan Data --}}
                        <div style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; border-radius: 0.75rem !important; padding: 0.75rem 1rem !important;">
                            <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                <span style="color: #64748b !important; font-weight: 500 !important;">Petugas Bertugas</span>
                                <span style="color: #0f172a !important; font-weight: 700 !important;">{{ $createdAttendance->user?->name ?? '-' }}</span>
                            </div>

                            <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                <span style="color: #64748b !important; font-weight: 500 !important;">Petugas Sebelumnya</span>
                                <span style="color: #334155 !important; font-weight: 600 !important;">{{ $createdAttendance->previousSecurity?->name ?? '-' }}</span>
                            </div>

                            <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                <span style="color: #64748b !important; font-weight: 500 !important;">Jam Tugas Shift</span>
                                <span style="color: #15803d !important; font-weight: 700 !important;">
                                    @if ($createdAttendance->start_time && $createdAttendance->end_time)
                                        {{ \Illuminate\Support\Carbon::parse($createdAttendance->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($createdAttendance->end_time)->format('H:i') }} WIB
                                    @else
                                        {{ \Illuminate\Support\Carbon::parse($createdAttendance->attendance_time)->format('H:i') }} WIB
                                    @endif
                                </span>
                            </div>

                            <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                <span style="color: #64748b !important; font-weight: 500 !important;">Total Durasi Kerja</span>
                                <span style="color: #0284c7 !important; font-weight: 700 !important;">{{ $createdAttendance->work_duration }}</span>
                            </div>

                            @if ($createdAttendance->location_address)
                                <div style="display: flex !important; justify-content: space-between !important; gap: 0.5rem !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                    <span style="color: #64748b !important; font-weight: 500 !important; flex-shrink: 0 !important;">Lokasi Pos</span>
                                    <span style="color: #334155 !important; font-weight: 500 !important; text-align: right !important; font-size: 0.75rem !important;">{{ $createdAttendance->location_address }}</span>
                                </div>
                            @endif

                            @if ($createdAttendance->latitude && $createdAttendance->longitude)
                                <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                                    <span style="color: #64748b !important; font-weight: 500 !important;">Koordinat GPS</span>
                                    <a
                                        href="https://www.google.com/maps?q={{ $createdAttendance->latitude }},{{ $createdAttendance->longitude }}"
                                        target="_blank"
                                        style="color: #059669 !important; font-weight: 600 !important; text-decoration: underline !important; font-size: 0.75rem !important;"
                                    >
                                        📍 {{ number_format($createdAttendance->latitude, 5) }}, {{ number_format($createdAttendance->longitude, 5) }} ↗
                                    </a>
                                </div>
                            @endif

                            @if ($createdAttendance->notes)
                                <div style="display: flex !important; justify-content: space-between !important; gap: 0.5rem !important; padding: 0.35rem 0 !important; font-size: 0.8rem !important;">
                                    <span style="color: #64748b !important; font-weight: 500 !important; flex-shrink: 0 !important;">Catatan</span>
                                    <span style="color: #475569 !important; font-style: italic !important; text-align: right !important; font-size: 0.75rem !important;">{{ $createdAttendance->notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Alert Info Status Screenshot / Copy Clipboard --}}
                <div
                    id="attendance-screenshot-alert"
                    style="display: none; padding: 0.6rem 1rem !important; background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 0.75rem !important; font-weight: 600 !important; text-align: center !important; border-top: 1px solid #a7f3d0 !important;"
                >
                    ✅ Screenshot berhasil disalin ke clipboard & diunduh! Langsung Paste (Ctrl+V) di chat WhatsApp.
                </div>

                {{-- Footer Modal: Tombol Bagikan Screenshot ke WhatsApp & Selesai --}}
                <div style="padding: 0.875rem 1.25rem !important; background-color: #f8fafc !important; border-top: 1px solid #e2e8f0 !important; display: flex !important; gap: 0.625rem !important; justify-content: flex-end !important; align-items: center !important; flex-shrink: 0 !important;">
                    <button
                        type="button"
                        wire:click="closeModalAndRedirect"
                        style="padding: 0.5rem 0.875rem !important; background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; border-radius: 0.5rem !important; font-size: 0.8rem !important; font-weight: 600 !important; color: #475569 !important; cursor: pointer !important; transition: all 0.2s ease !important;"
                    >
                        Selesai & Ke Log
                    </button>

                    <button
                        type="button"
                        id="btn-share-whatsapp-screenshot"
                        onclick="window.shareAttendanceCardScreenshot('{{ $createdAttendance->whatsapp_url }}', @js($createdAttendance->whatsapp_text))"
                        style="padding: 0.5rem 1rem !important; background-color: #25D366 !important; color: #ffffff !important; border: none !important; border-radius: 0.5rem !important; font-size: 0.8rem !important; font-weight: 700 !important; display: inline-flex !important; align-items: center !important; gap: 0.4rem !important; box-shadow: 0 2px 4px rgba(37, 211, 102, 0.3) !important; cursor: pointer !important; transition: all 0.2s ease !important;"
                    >
                        <svg style="width: 1rem !important; height: 1rem !important; fill: currentColor !important;" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span id="btn-share-text">Kirim Screenshot ke WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
