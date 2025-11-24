import AppLayout from '@/layouts/app-layout';

export default function Index({ devices }: any) {
    return (
        <AppLayout>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <h1>Devices</h1>
                    <div>
                        {devices.data && devices.data.map((device: any) => (
                            <div key={device.id}>{device.name}</div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
