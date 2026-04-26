export function formatCurrency(value: number, decimals: number): string {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(Math.abs(value));
}

export function splitNumber(value: number, decimals: number): { whole: string; decimal: string } {
    const formatted = formatCurrency(value, decimals);
    const parts = formatted.split('.');
    return { whole: parts[0], decimal: parts[1] ?? '' };
}

export function splitName(name: string): { lead: string; em: string } {
    if (!name) return { lead: '', em: '' };
    const parts = name.split(/\s+/);
    if (parts.length === 1) return { lead: '', em: parts[0] };
    return { lead: parts.slice(0, -1).join(' '), em: parts[parts.length - 1] };
}

const memberPalette = [
    'bg-brand-soft text-brand',
    'bg-[oklch(0.94_0.04_200)] text-[oklch(0.45_0.12_220)]',
    'bg-[oklch(0.94_0.04_130)] text-[oklch(0.4_0.11_145)]',
    'bg-[oklch(0.94_0.04_290)] text-[oklch(0.45_0.13_290)]',
];

const dotPalette = [
    'bg-brand',
    'bg-[oklch(0.55_0.12_220)]',
    'bg-[oklch(0.5_0.12_145)]',
    'bg-[oklch(0.55_0.14_290)]',
    'bg-[oklch(0.6_0.13_80)]',
    'bg-muted-foreground',
];

export function memberClass(idx: number): string {
    return memberPalette[idx % memberPalette.length];
}

export function dotClass(idx: number): string {
    return dotPalette[idx % dotPalette.length];
}
