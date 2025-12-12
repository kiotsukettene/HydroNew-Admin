import { useCallback } from 'react';

export function useInitials() {
    return useCallback((firstName?: string, lastName?: string): string => {
        if (!firstName && !lastName) return '';

        const firstInitial = firstName?.trim().charAt(0) ?? '';
        const lastInitial = lastName?.trim().charAt(0) ?? '';

        return `${firstInitial}${lastInitial}`.toUpperCase();
    }, []);
}
