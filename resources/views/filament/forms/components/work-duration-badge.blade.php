<div
    x-data="{
        startTime: $wire.$entangle('data.start_time'),
        endTime: $wire.$entangle('data.end_time'),

        get durationInfo() {
            if (!this.startTime || !this.endTime) return null;
            try {
                const [startH, startM] = this.startTime.split(':').map(Number);
                const [endH, endM] = this.endTime.split(':').map(Number);
                
                const startTotal = startH * 60 + (startM || 0);
                const endTotal = endH * 60 + (endM || 0);

                let diff = endTotal - startTotal;
                let isNextDay = false;

                if (diff <= 0) {
                    diff = (24 * 60 - startTotal) + endTotal;
                    isNextDay = true;
                }

                const hours = Math.floor(diff / 60);
                const minutes = diff % 60;
                let text = hours + ' Jam';
                if (minutes > 0) {
                    text += ' ' + minutes + ' Menit';
                }

                return {
                    text: text,
                    isNextDay: isNextDay,
                    hours: hours
                };
            } catch (e) {
                return null;
            }
        }
    }"
    class="w-full"
>
    <template x-if="durationInfo">
        <div class="flex items-center justify-between rounded-xl border border-sky-200 bg-sky-50/80 px-3.5 py-2.5 text-xs text-sky-900 shadow-sm dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-200">
            <div class="flex items-center gap-2">
                <span class="text-base">⏱️</span>
                <span>
                    <strong>Total Durasi Kerja:</strong>
                    <span class="ml-1 inline-flex items-center rounded-md bg-sky-200/80 px-2 py-0.5 font-bold text-sky-950 dark:bg-sky-800 dark:text-sky-100" x-text="durationInfo.text"></span>
                </span>
            </div>
            <template x-if="durationInfo.isNextDay">
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    🌙 Shift Malam (+1 Hari)
                </span>
            </template>
            <template x-if="!durationInfo.isNextDay">
                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                    ☀️ Shift Hari Sama
                </span>
            </template>
        </div>
    </template>
</div>
