@php
    $selfieSrc = $record->selfie_url;
    $cardId = 'attendance-share-card-' . $record->id;
    $btnId = 'btn-share-wa-' . $record->id;
    $btnTextId = 'btn-share-text-' . $record->id;
    $alertId = 'attendance-share-alert-' . $record->id;
@endphp

<div style="display: flex; flex-direction: column; gap: 1rem; width: 100%;">
    {{-- AREA KARTU POPUP YANG AKAN DI-SCREENSHOT --}}
    <div id="{{ $cardId }}" style="background-color: #ffffff !important; border-radius: 1rem !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1) !important; border: 1px solid #e2e8f0 !important; overflow: hidden !important; display: flex !important; flex-direction: column !important; width: 100% !important; max-width: 480px !important; margin: 0 auto !important;">
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
                    {{ $record->day_name }}, {{ \Illuminate\Support\Carbon::parse($record->attendance_date)->translatedFormat('d M Y') }} • {{ \Illuminate\Support\Carbon::parse($record->attendance_time)->format('H:i') }} WIB
                </p>
            </div>
        </div>

        {{-- Body Modal (Foto + Ringkasan) --}}
        <div style="padding: 1rem 1.25rem !important; display: flex !important; flex-direction: column !important; gap: 0.875rem !important; background-color: #ffffff !important;">
            {{-- Foto Selfie --}}
            @if ($selfieSrc)
                <div style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; border-radius: 0.75rem !important; padding: 0.5rem !important; text-align: center !important;">
                    <img
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
                    <span style="color: #0f172a !important; font-weight: 700 !important;">{{ $record->user?->name ?? '-' }}</span>
                </div>

                <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                    <span style="color: #64748b !important; font-weight: 500 !important;">Petugas Sebelumnya</span>
                    <span style="color: #334155 !important; font-weight: 600 !important;">{{ $record->previousSecurity?->name ?? '-' }}</span>
                </div>

                <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                    <span style="color: #64748b !important; font-weight: 500 !important;">Jam Tugas Shift</span>
                    <span style="color: #15803d !important; font-weight: 700 !important;">
                        @if ($record->start_time && $record->end_time)
                            {{ \Illuminate\Support\Carbon::parse($record->start_time)->format('H:i') }} - {{ \Illuminate\Support\Carbon::parse($record->end_time)->format('H:i') }} WIB
                        @else
                            {{ \Illuminate\Support\Carbon::parse($record->attendance_time)->format('H:i') }} WIB
                        @endif
                    </span>
                </div>

                <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                    <span style="color: #64748b !important; font-weight: 500 !important;">Total Durasi Kerja</span>
                    <span style="color: #0284c7 !important; font-weight: 700 !important;">{{ $record->work_duration }}</span>
                </div>

                @if ($record->location_address)
                    <div style="display: flex !important; justify-content: space-between !important; gap: 0.5rem !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                        <span style="color: #64748b !important; font-weight: 500 !important; flex-shrink: 0 !important;">Lokasi Pos</span>
                        <span style="color: #334155 !important; font-weight: 500 !important; text-align: right !important; font-size: 0.75rem !important;">{{ $record->location_address }}</span>
                    </div>
                @endif

                @if ($record->latitude && $record->longitude)
                    <div style="display: flex !important; justify-content: space-between !important; padding: 0.35rem 0 !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.8rem !important;">
                        <span style="color: #64748b !important; font-weight: 500 !important;">Koordinat GPS</span>
                        <a
                            href="https://www.google.com/maps?q={{ $record->latitude }},{{ $record->longitude }}"
                            target="_blank"
                            style="color: #059669 !important; font-weight: 600 !important; text-decoration: underline !important; font-size: 0.75rem !important;"
                        >
                            📍 {{ number_format($record->latitude, 5) }}, {{ number_format($record->longitude, 5) }} ↗
                        </a>
                    </div>
                @endif

                @if ($record->notes)
                    <div style="display: flex !important; justify-content: space-between !important; gap: 0.5rem !important; padding: 0.35rem 0 !important; font-size: 0.8rem !important;">
                        <span style="color: #64748b !important; font-weight: 500 !important; flex-shrink: 0 !important;">Catatan</span>
                        <span style="color: #475569 !important; font-style: italic !important; text-align: right !important; font-size: 0.75rem !important;">{{ $record->notes }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Alert Info Status Screenshot --}}
    <div
        id="{{ $alertId }}"
        style="display: none; padding: 0.6rem 1rem !important; background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 0.75rem !important; font-weight: 600 !important; text-align: center !important; border-radius: 0.5rem !important; border: 1px solid #a7f3d0 !important;"
    >
        ✅ Screenshot berhasil disalin ke clipboard & diunduh! Langsung Paste (Ctrl+V) di WhatsApp.
    </div>

    {{-- Tombol Aksi Screenshot --}}
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; width: 100%; max-width: 480px; margin: 0 auto;">
        <button
            type="button"
            id="{{ $btnId }}"
            onclick="window.shareSingleAttendanceScreenshot('{{ $cardId }}', '{{ $btnId }}', '{{ $btnTextId }}', '{{ $alertId }}', '{{ $record->whatsapp_url }}', @js($record->whatsapp_text))"
            style="width: 100% !important; padding: 0.65rem 1.25rem !important; background-color: #25D366 !important; color: #ffffff !important; border: none !important; border-radius: 0.75rem !important; font-size: 0.875rem !important; font-weight: 700 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; gap: 0.5rem !important; box-shadow: 0 4px 6px -1px rgba(37, 211, 102, 0.25) !important; cursor: pointer !important; transition: all 0.2s ease !important;"
        >
            <svg style="width: 1.15rem !important; height: 1.15rem !important; fill: currentColor !important;" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            <span id="{{ $btnTextId }}">Kirim Screenshot ke WhatsApp</span>
        </button>
    </div>
</div>
