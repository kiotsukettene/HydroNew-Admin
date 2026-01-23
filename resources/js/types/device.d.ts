export interface Device {
    id: number;
    device_name: string;
    serial_number: string;
    model?: string | null;
    firmware_version?: string | null;
    status: 'online' | 'offline' | null;
    is_archived: boolean;
    created_at: string;
    updated_at: string;
    users?: Array<{
        id: number;
        first_name: string;
        last_name: string;
        email: string;
    }>;
}
