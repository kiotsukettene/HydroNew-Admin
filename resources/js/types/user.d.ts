export interface User {
    id: number;
    usersCount: number;
    first_name: string;
    last_name: string;
    email: string;
    address: string | null;
    status: string;
    email_verified_at: string | null;
}
