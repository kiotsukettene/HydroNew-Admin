import AppLogoIcon from '@/components/app-logo-icon';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

export default function AuthCardLayout({
    children,
    title,
    description,
}: PropsWithChildren<{
    name?: string;
    title?: string;
    description?: string;
}>) {
    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-linear-to-b from-white to-[#FFF5E0] p-6 md:p-10 dark:from-gray-950 dark:to-gray-900/95">
            <div className="flex w-full max-w-md flex-col gap-6">

                <div className="flex flex-col gap-6">
                    <Card className="rounded-xl">
                        <CardHeader className="px-10 pt-8 pb-0 text-center">
                            <Link
                                href={home().url}
                                className="flex items-center gap-2 justify-center font-medium mb-4"
                            >
                                  <div className="flex items-center justify-center mx-auto scale-150 mb-3">
                    <AppLogoIcon className="fill-current text-black dark:text-white" />
                </div>

                            </Link>
                            <CardTitle className="text-xl">{title}</CardTitle>
                            <CardDescription>{description}</CardDescription>
                        </CardHeader>
                        <CardContent className="px-10 py-8">
                            {children}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    );
}
