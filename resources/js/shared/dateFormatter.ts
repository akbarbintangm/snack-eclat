export function formatDisplayDate(value?: string | null): string {
    const parts = parseDateParts(value);

    if (!parts) {
        return value || '-';
    }

    return `${pad(parts.day)}/${pad(parts.month)}/${parts.year}`;
}

export function formatDisplayDateTime(value?: string | null): string {
    if (!value) {
        return '-';
    }

    const dateTime = parseDateTime(value);

    if (!dateTime) {
        return value;
    }

    return `${formatDisplayDate(dateTime.date)} ${pad(dateTime.hours)}:${pad(dateTime.minutes)}:${pad(dateTime.seconds)}`;
}

export function formatDisplayDateRange(dateFrom?: string | null, dateTo?: string | null): string {
    if (!dateFrom && !dateTo) {
        return 'Semua Periode';
    }

    if (dateFrom && dateTo && dateFrom !== dateTo) {
        return `${formatDisplayDate(dateFrom)} - ${formatDisplayDate(dateTo)}`;
    }

    return formatDisplayDate(dateFrom ?? dateTo);
}

export function formatDateInput(value?: string | null): string {
    const parts = parseDateParts(value);

    if (!parts) {
        return '';
    }

    return `${pad(parts.day)}/${pad(parts.month)}/${parts.year}`;
}

export function parseDateInput(value?: string | null): string {
    const parts = parseDateParts(value);

    if (!parts) {
        return '';
    }

    return `${parts.year}-${pad(parts.month)}-${pad(parts.day)}`;
}

export function normalizeDateInput(value: string): string {
    return formatDateInput(value) || value.trim();
}

export function openNativeDatePicker(event: Event): void {
    const input = event.currentTarget as HTMLInputElement & { showPicker?: () => void };

    try {
        input.showPicker?.();
    } catch {
        input.focus();
    }
}

function parseDateParts(value?: string | null): { day: number; month: number; year: number } | null {
    if (!value) {
        return null;
    }

    const trimmed = value.trim();
    const iso = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})/);

    if (iso) {
        return validDateParts(Number(iso[3]), Number(iso[2]), Number(iso[1]));
    }

    const display = trimmed.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);

    if (display) {
        return validDateParts(Number(display[1]), Number(display[2]), Number(display[3]));
    }

    const parsed = new Date(trimmed);

    if (Number.isNaN(parsed.getTime())) {
        return null;
    }

    return {
        day: parsed.getDate(),
        month: parsed.getMonth() + 1,
        year: parsed.getFullYear(),
    };
}

function parseDateTime(value: string): { date: string; hours: number; minutes: number; seconds: number } | null {
    const hasTimezone = /(?:Z|[+-]\d{2}:?\d{2})$/.test(value);

    if (hasTimezone) {
        return dateObjectToDateTime(new Date(value));
    }

    const sqlDateTime = value.match(/^(\d{4}-\d{2}-\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);

    if (sqlDateTime) {
        return {
            date: sqlDateTime[1],
            hours: Number(sqlDateTime[2]),
            minutes: Number(sqlDateTime[3]),
            seconds: Number(sqlDateTime[4] ?? 0),
        };
    }

    return dateObjectToDateTime(new Date(value));
}

function dateObjectToDateTime(date: Date): { date: string; hours: number; minutes: number; seconds: number } | null {
    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return {
        date: `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`,
        hours: date.getHours(),
        minutes: date.getMinutes(),
        seconds: date.getSeconds(),
    };
}

function validDateParts(day: number, month: number, year: number): { day: number; month: number; year: number } | null {
    const date = new Date(year, month - 1, day);

    if (date.getFullYear() !== year || date.getMonth() !== month - 1 || date.getDate() !== day) {
        return null;
    }

    return { day, month, year };
}

function pad(value: number): string {
    return String(value).padStart(2, '0');
}
