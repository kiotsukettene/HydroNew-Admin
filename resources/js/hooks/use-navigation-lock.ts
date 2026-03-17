import { router } from '@inertiajs/react';
import { useCallback, useEffect, useRef } from 'react';

export function useNavigationLock() {
    const navigating = useRef(false);

    useEffect(() => {
        const removeStart = router.on('start', () => {
            navigating.current = true;
        });
        const removeFinish = router.on('finish', () => {
            navigating.current = false;
        });

        return () => {
            removeStart();
            removeFinish();
        };
    }, []);

    const isNavigating = useCallback(() => navigating.current, []);

    return { isNavigating };
}
