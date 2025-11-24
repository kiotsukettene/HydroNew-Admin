import AppLayout from '@/layouts/app-layout';

export default function Show({ device }: any) {
    return (
        <AppLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1>{device.name}</h1>
                </div>
            </div>
        </AppLayout>
    );
}
