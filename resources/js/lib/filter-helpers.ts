/**
 * Remove default/empty filter values from query parameters
 * This keeps URLs clean by not showing default filter states
 */
export const cleanFilters = <T extends Record<string, any>>(
    filters: T,
    defaults: Partial<T> = {}
): Partial<T> => {
    const cleaned: Partial<T> = {};

    for (const key in filters) {
        const value = filters[key];
        const defaultValue = defaults[key];

        // Skip if value is undefined or null
        if (value === undefined || value === null) {
            continue;
        }

        // Skip if value is empty string
        if (value === '') {
            continue;
        }

        // Skip if value equals default
        if (defaultValue !== undefined && value === defaultValue) {
            continue;
        }

        // Skip if value is 'all' (common default for filters)
        if (value === 'all') {
            continue;
        }

        cleaned[key] = value;
    }

    return cleaned;
};
