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
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    seo: SeoProps;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    roles?: string[];
}

export type BreadcrumbItemType = BreadcrumbItem;
