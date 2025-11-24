import AppLayout from '@/layouts/app-layout';

export default function Edit({ device }: any) {
    return (
        <AppLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1>Edit Device</h1>
                    <p>{device?.name}</p>
                </div>
            </div>
        </AppLayout>
    );
}
