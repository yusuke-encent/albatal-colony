export type ProviderSummary = {
    id: number;
    name: string;
};

export type GenreSummary = {
    id: number;
    name: string;
    slug?: string;
};

export type TagSummary = {
    id: number;
    name: string;
    slug: string;
};

export type ContentCard = {
    id: number;
    sku: string | null;
    title: string;
    slug: string;
    price: number;
    formatted_price: string;
    product_code?: string | null;
    cover_url: string | null;
    preview_urls: string[];
    provider: ProviderSummary;
    genre: GenreSummary;
    tags: TagSummary[];
};
