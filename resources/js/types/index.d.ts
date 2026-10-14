import type { LucideIcon } from 'lucide-vue-next';
import type { Component } from 'vue';

export interface SeoProps {
    site_name: string;
    default_description: string;
    default_og_image: string;
    locale: string;
    url: string;
}

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | Component;
    isActive?: boolean;
    disabled?: boolean;
    kbd?: string;
}

export interface CurrencyOption {
    currency_id: number;
    uuid: string;
    name: string;
    code: string;
    symbol: string;
}

export interface SidebarTally {
    uuid: string;
    name: string;
    color: string | null;
    glyph: string | null;
    monthly_totals: { amount: number; currency_symbol: string; currency_code: string; decimals: number }[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    /** Shared as an Inertia once prop; absent for guests. */
    sidebar_tallies?: SidebarTally[];
    /** The user's primary currency; shared as an Inertia once prop, absent for guests. */
    primary_currency?: { symbol: string; code: string; decimals: number };
    /** Supported currencies for the default-currency selects; shared as an Inertia once prop, absent for guests. */
    currencies?: CurrencyOption[];
    seo: SeoProps;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    roles?: string[];
    default_currency: { uuid: string; code: string; symbol: string } | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
