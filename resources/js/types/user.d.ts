export interface User {
    id: number;
    usersCount: number;
    first_name: string;
    last_name: string;
    email: string;
    address: string | null;
    email_verified_at: string | null;
    status: 'active' | 'inactive';
    is_archived: boolean;
    last_login_at: string | null;
    role?: string;
    profile_picture?: string | null;
}
