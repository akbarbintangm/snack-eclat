const dateFormatter = new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

export function formatDisplayDate(value?: string | null): string {
    if (!value) {
        return '-';
    }

    const normalized = value.includes('T') ? value.slice(0, 10) : value;
    const match = normalized.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    const date = match
        ? new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]))
        : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return dateFormatter.format(date);
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

export function openNativeDatePicker(event: Event): void {
    const input = event.currentTarget as HTMLInputElement & { showPicker?: () => void };

    try {
        input.showPicker?.();
    } catch {
        input.focus();
    }
}
