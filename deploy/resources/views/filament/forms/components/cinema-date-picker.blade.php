<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <style>
        .cdp-wrapper {
            font-family: inherit;
            color: #1e293b;
        }
        .cdp-calendar-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }
        .dark .cdp-calendar-card {
            background-color: #0f172a;
            border-color: #1e293b;
            color: #f8fafc;
        }
        .cdp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .cdp-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .dark .cdp-title {
            color: #ffffff;
        }
        .cdp-nav {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .cdp-nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 0.375rem;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .cdp-nav-btn:hover:not(:disabled) {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .dark .cdp-nav-btn:hover:not(:disabled) {
            background-color: #1e293b;
            color: #ffffff;
        }
        .cdp-nav-btn:disabled {
            opacity: 0.25;
            cursor: not-allowed;
        }
        .cdp-days-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        .cdp-days-header .text-weekend {
            color: #f43f5e;
        }
        .cdp-days-header .text-weekday {
            color: #64748b;
        }
        .dark .cdp-days-header .text-weekday {
            color: #94a3b8;
        }
        .cdp-dates-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            row-gap: 0.75rem;
            column-gap: 0.375rem;
            text-align: center;
        }
        .cdp-cell-blank {
            height: 3rem;
            width: 100%;
        }
        .cdp-cell-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 3rem;
            width: 100%;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            background: transparent;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.12s ease;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }
        .cdp-cell-btn.is-weekday {
            color: #64748b;
        }
        .dark .cdp-cell-btn.is-weekday {
            color: #cbd5e1;
        }
        .cdp-cell-btn.is-weekend {
            color: #f43f5e;
        }
        .cdp-cell-btn.is-today:not(.is-selected) {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #f43f5e;
            font-weight: 600;
        }
        .dark .cdp-cell-btn.is-today:not(.is-selected) {
            background-color: #1e293b;
            border-color: #334155;
        }
        .cdp-cell-btn:hover:not(.is-selected):not(.is-disabled) {
            background-color: #f1f5f9;
        }
        .dark .cdp-cell-btn:hover:not(.is-selected):not(.is-disabled) {
            background-color: #1e293b;
        }
        .cdp-cell-btn.is-selected {
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
        }
        .cdp-cell-btn.is-disabled {
            color: #cbd5e1 !important;
            opacity: 0.35;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        .dark .cdp-cell-btn.is-disabled {
            color: #475569 !important;
        }

        /* Summary Box */
        .cdp-summary-card {
            margin-top: 1rem;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }
        .dark .cdp-summary-card {
            background-color: #0f172a;
            border-color: #1e293b;
        }
        .cdp-summary-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cdp-summary-title {
            font-size: 0.9375rem;
            color: #334155;
        }
        .dark .cdp-summary-title {
            color: #cbd5e1;
        }
        .cdp-summary-title strong {
            color: #0f172a;
            font-weight: 700;
        }
        .dark .cdp-summary-title strong {
            color: #ffffff;
        }
        .cdp-summary-title .workday-hint {
            font-size: 0.8125rem;
            color: #64748b;
            font-weight: 400;
        }
        .dark .cdp-summary-title .workday-hint {
            color: #94a3b8;
        }
        .cdp-btn-clear {
            font-size: 0.875rem;
            font-weight: 500;
            color: #f43f5e;
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .cdp-btn-clear:hover {
            color: #e11d48;
            text-decoration: underline;
        }
        .cdp-chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.875rem;
        }
        .cdp-chip-weekend {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: 1px solid #fecdd3;
            background-color: #fff1f2;
            color: #e11d48;
            padding: 0.3125rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .dark .cdp-chip-weekend {
            background-color: rgba(225, 29, 72, 0.15);
            border-color: rgba(225, 29, 72, 0.4);
            color: #fda4af;
        }
        .cdp-chip-weekday {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border: 1px solid #bfdbfe;
            background-color: #eff6ff;
            color: #1d4ed8;
            padding: 0.3125rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .dark .cdp-chip-weekday {
            background-color: rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.4);
            color: #93c5fd;
        }
        .cdp-chip-remove {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1rem;
            height: 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
            background: transparent;
            border: none;
            opacity: 0.7;
            transition: opacity 0.15s ease;
        }
        .cdp-chip-remove:hover {
            opacity: 1;
        }

        /* Callout Box */
        .cdp-callout-card {
            margin-top: 1rem;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 0.75rem;
            padding: 0.875rem 1.25rem;
            font-size: 0.9375rem;
            color: #334155;
        }
        .dark .cdp-callout-card {
            background-color: rgba(30, 41, 59, 0.5);
            border-color: #1e293b;
            color: #cbd5e1;
        }
        .cdp-callout-card strong {
            color: #0f172a;
            font-weight: 700;
        }
        .dark .cdp-callout-card strong {
            color: #ffffff;
        }
        .cdp-rule-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
    </style>

    <div
        x-data="cinemaDatePicker({
            state: $wire.$entangle('{{ $getStatePath() }}'),
            leaveType: $wire.$entangle('data.type'),
            maxDate: '{{ now()->addMonths(6)->format('Y-m-d') }}',
            todayDate: '{{ now()->format('Y-m-d') }}',
            initialYear: {{ now()->year }},
            initialMonth: {{ now()->month - 1 }}
        })"
        class="cdp-wrapper"
    >
        <!-- Kalender Card -->
        <div class="cdp-calendar-card">
            <!-- Header Bulan & Panah Navigasi -->
            <div class="cdp-header">
                <div class="cdp-title" x-text="monthYearLabel"></div>

                <div class="cdp-nav">
                    <button
                        type="button"
                        @click="prevMonth"
                        :disabled="isPrevDisabled"
                        class="cdp-nav-btn"
                        title="Bulan sebelumnya"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                    </button>

                    <button
                        type="button"
                        @click="nextMonth"
                        :disabled="isNextDisabled"
                        class="cdp-nav-btn"
                        title="Bulan berikutnya"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Nama Hari -->
            <div class="cdp-days-header">
                <div class="text-weekend">Min</div>
                <div class="text-weekday">Sen</div>
                <div class="text-weekday">Sel</div>
                <div class="text-weekday">Rab</div>
                <div class="text-weekday">Kam</div>
                <div class="text-weekday">Jum</div>
                <div class="text-weekend">Sab</div>
            </div>

            <!-- Grid Tanggal -->
            <div class="cdp-dates-grid">
                <!-- Padding Blank Awal Bulan -->
                <template x-for="blank in blankDays" :key="'blank-' + blank">
                    <div class="cdp-cell-blank"></div>
                </template>

                <!-- Tombol Tanggal -->
                <template x-for="day in daysInMonth" :key="day.dateStr">
                    <button
                        type="button"
                        @click="toggleDate(day.dateStr)"
                        :disabled="day.disabled"
                        :class="{
                            'is-selected': isSelected(day.dateStr),
                            'is-today': isToday(day.dateStr) && !isSelected(day.dateStr),
                            'is-weekend': day.isWeekend && !isSelected(day.dateStr),
                            'is-weekday': !day.isWeekend && !isSelected(day.dateStr),
                            'is-disabled': day.disabled
                        }"
                        class="cdp-cell-btn"
                    >
                        <span x-text="day.dayNumber"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Box Total Dipilih & Chips -->
        <div class="cdp-summary-card">
            <div class="cdp-summary-header">
                <div class="cdp-summary-title">
                    Total Dipilih: <strong x-text="selectedCountText"></strong>
                    <span class="workday-hint" x-text="'(' + workDaysCount + ' Hari Kerja)'"></span>
                </div>

                <template x-if="state && state.length > 0">
                    <button
                        type="button"
                        @click="clearAll"
                        class="cdp-btn-clear"
                    >
                        Hapus Semua
                    </button>
                </template>
            </div>

            <!-- Pill / Chip Tanggal -->
            <div class="cdp-chips-container">
                <template x-for="item in selectedDateDetails" :key="item.dateStr">
                    <span
                        :class="item.isWeekend ? 'cdp-chip-weekend' : 'cdp-chip-weekday'"
                    >
                        <span x-text="item.label"></span>
                        <button
                            type="button"
                            @click="toggleDate(item.dateStr)"
                            class="cdp-chip-remove"
                            title="Hapus tanggal ini"
                        >
                            &times;
                        </button>
                    </span>
                </template>

                <template x-if="!state || state.length === 0">
                    <p style="font-size: 0.875rem; font-style: italic; color: #94a3b8; margin: 0;">
                        Belum ada tanggal yang dipilih. Silakan klik tanggal pada kalender di atas.
                    </p>
                </template>
            </div>
        </div>

        <!-- Callout Hari Kerja & Keterangan Aturan Backdate -->
        <div class="cdp-callout-card">
            <div>
                Tanggal yang dipilih mencakup <strong x-text="workDaysCount + ' hari kerja'"></strong>.
            </div>
            <div class="cdp-rule-hint" x-text="ruleHintText"></div>
        </div>
    </div>

    <script>
        function cinemaDatePicker(config) {
            const monthNames = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const dayNamesShort = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
            const monthNamesShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            return {
                state: config.state || [],
                leaveType: config.leaveType || 'cuti',
                maxDate: config.maxDate,
                todayDate: config.todayDate,
                year: config.initialYear,
                month: config.initialMonth,

                init() {
                    if (!Array.isArray(this.state)) {
                        this.state = this.state ? [this.state] : [];
                    }
                    this.$watch('leaveType', (newVal) => {
                        this.pruneInvalidDates();
                    });
                },

                get isBackdateAllowed() {
                    return this.leaveType === 'izin_darurat' || this.leaveType === 'sakit';
                },

                get minDate() {
                    if (this.isBackdateAllowed) {
                        // Maksimal 14 hari backdate
                        const d = new Date(this.todayDate + 'T00:00:00');
                        d.setDate(d.getDate() - 14);
                        const mStr = String(d.getMonth() + 1).padStart(2, '0');
                        const dStr = String(d.getDate()).padStart(2, '0');
                        return `${d.getFullYear()}-${mStr}-${dStr}`;
                    }
                    // Cuti biasa: tidak bisa backdate
                    return this.todayDate;
                },

                get ruleHintText() {
                    if (this.isBackdateAllowed) {
                        return 'ℹ️ Jenis ' + (this.leaveType === 'sakit' ? 'Sakit' : 'Izin Darurat') + ' diperbolehkan backdate maksimal 14 hari ke belakang.';
                    }
                    return 'ℹ️ Jenis Cuti hanya dapat memilih tanggal hari ini ke depan (tidak dapat backdate).';
                },

                get monthYearLabel() {
                    return monthNames[this.month] + ' ' + this.year;
                },

                get isPrevDisabled() {
                    const minD = new Date(this.minDate + 'T00:00:00');
                    return this.year < minD.getFullYear() ||
                        (this.year === minD.getFullYear() && this.month <= minD.getMonth());
                },

                get isNextDisabled() {
                    const maxD = new Date(this.maxDate + 'T00:00:00');
                    return this.year > maxD.getFullYear() ||
                        (this.year === maxD.getFullYear() && this.month >= maxD.getMonth());
                },

                get blankDays() {
                    const firstDayIndex = new Date(this.year, this.month, 1).getDay(); // 0 = Min, 6 = Sab
                    return Array.from({ length: firstDayIndex }, (_, i) => i);
                },

                get daysInMonth() {
                    const totalDays = new Date(this.year, this.month + 1, 0).getDate();
                    const days = [];
                    const minD = new Date(this.minDate + 'T00:00:00');
                    const maxD = new Date(this.maxDate + 'T23:59:59');

                    for (let d = 1; d <= totalDays; d++) {
                        const mStr = String(this.month + 1).padStart(2, '0');
                        const dStr = String(d).padStart(2, '0');
                        const dateStr = `${this.year}-${mStr}-${dStr}`;
                        const curD = new Date(`${this.year}-${mStr}-${dStr}T00:00:00`);
                        const dayOfWeek = curD.getDay();
                        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                        const disabled = curD < minD || curD > maxD;

                        days.push({
                            dayNumber: d,
                            dateStr: dateStr,
                            isWeekend: isWeekend,
                            disabled: disabled
                        });
                    }

                    return days;
                },

                get selectedCountText() {
                    const count = (this.state && Array.isArray(this.state)) ? this.state.length : 0;
                    return count + ' Tanggal';
                },

                get workDaysCount() {
                    if (!this.state || !Array.isArray(this.state)) return 0;
                    let workDays = 0;
                    this.state.forEach(dStr => {
                        const d = new Date(dStr + 'T00:00:00');
                        const dayOfWeek = d.getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                            workDays++;
                        }
                    });
                    return workDays;
                },

                get selectedDateDetails() {
                    if (!this.state || !Array.isArray(this.state)) return [];
                    const sorted = [...this.state].sort();
                    return sorted.map(dStr => {
                        const curD = new Date(dStr + 'T00:00:00');
                        const dayOfWeek = curD.getDay();
                        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                        const dayName = dayNamesShort[dayOfWeek];
                        const dayNum = curD.getDate();
                        const monthName = monthNamesShort[curD.getMonth()];
                        const year = curD.getFullYear();

                        let label = `${dayName}, ${dayNum} ${monthName} ${year}`;
                        if (isWeekend) {
                            label += ' (Akhir pekan)';
                        }

                        return {
                            dateStr: dStr,
                            isWeekend: isWeekend,
                            label: label
                        };
                    });
                },

                pruneInvalidDates() {
                    if (!Array.isArray(this.state)) return;
                    const minAllowed = this.minDate;
                    this.state = this.state.filter(d => d >= minAllowed);
                },

                prevMonth() {
                    if (this.isPrevDisabled) return;
                    if (this.month === 0) {
                        this.month = 11;
                        this.year--;
                    } else {
                        this.month--;
                    }
                },

                nextMonth() {
                    if (this.isNextDisabled) return;
                    if (this.month === 11) {
                        this.month = 0;
                        this.year++;
                    } else {
                        this.month++;
                    }
                },

                toggleDate(dateStr) {
                    if (!Array.isArray(this.state)) {
                        this.state = [];
                    }

                    const index = this.state.indexOf(dateStr);
                    if (index > -1) {
                        this.state.splice(index, 1);
                    } else {
                        this.state.push(dateStr);
                        this.state.sort();
                    }

                    this.state = [...this.state];
                },

                isSelected(dateStr) {
                    return Array.isArray(this.state) && this.state.includes(dateStr);
                },

                isToday(dateStr) {
                    return dateStr === this.todayDate;
                },

                clearAll() {
                    this.state = [];
                }
            };
        }
    </script>
</x-dynamic-component>
