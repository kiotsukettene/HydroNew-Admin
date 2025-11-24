import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

interface DashboardProps {
    stats?: {
        devices: number;
        sensors: number;
        setups: number;
    };
}

export default function Dashboard({ stats }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="space-y-6 p-6">
                <div>
                    <h1 className="text-3xl font-bold">Dashboard</h1>
                    <p className="mt-2 text-gray-600">Welcome to your hydroponic management system</p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <div className="rounded-lg border bg-white p-6 shadow">
                        <h3 className="text-sm font-medium text-gray-600">Total Devices</h3>
                        <p className="mt-2 text-4xl font-bold">{stats?.devices ?? 0}</p>
                    </div>
                    <div className="rounded-lg border bg-white p-6 shadow">
                        <h3 className="text-sm font-medium text-gray-600">Total Sensors</h3>
                        <p className="mt-2 text-4xl font-bold">{stats?.sensors ?? 0}</p>
                    </div>
                    <div className="rounded-lg border bg-white p-6 shadow">
                        <h3 className="text-sm font-medium text-gray-600">Active Setups</h3>
                        <p className="mt-2 text-4xl font-bold">{stats?.setups ?? 0}</p>
                    </div>
                </div>

                <div className="rounded-lg border bg-white p-6 shadow">
                    <h2 className="text-xl font-semibold">Quick Overview</h2>
                    <p className="mt-4 text-gray-600">
                        Your dashboard is ready. Start by adding devices and sensors to monitor your hydroponic systems.
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
