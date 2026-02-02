export interface Device {
    id: number;
    user_id: number;
    name: string;
    serial_number: string;
    status: string | null;
    created_at: string;
    updated_at: string;
    user?: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
    };
}
