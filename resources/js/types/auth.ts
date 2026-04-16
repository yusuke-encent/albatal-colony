export type User = {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'provider' | 'customer';
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type Flash = {
    success?: string | null;
    error?: string | null;
    generated_password?: string | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
