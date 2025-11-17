import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Cast,User } from 'lucide-react';



export default function Dashboard() {
    return (
        <AppLayout title="Dashboard">

            <Head title="Dashboard" />
<div className="w-full p-4 gap-5 overflow-hidden flex flex-col sm:flex-row">
            {/* <div className="text-2xl font-bold text-primary">Dashboard</div> */}


  <div className='sm:w-[60%] flex flex-col flex-wrap gap-4'>
    <Card className='bg-[#EFFFDD] shadow-none absolute sm:relative w-full overflow-hidden p-0'>
       <div className='px-8 pt-4 pb-0 mt-7'>
         <CardTitle className='text-3xl text-primary font-semibold'>Welcome to Dashboard!</CardTitle>
         <CardDescription className='text-md  font-light'>Project activity will be updated here. Click on the name section to set your configuration</CardDescription>
       </div>
       <div className='w-full'>
        <img src="/assets/dashboard-bg.svg" alt="Dashboard Illustration" className='w-full object-cover'/>
       </div>
    </Card>


    <div className=' flex flex-col gap-2 w-full'>
        <div className='font-medium text-lg'>Overview</div>
       <div className='flex flex-row space-x-4'>
         <Card className='p-6 w-full'>
            <CardTitle className='text-sm font-semibold text-muted-foreground'>Total Users</CardTitle>
            <div className="flex flex-row items-center justify-between ">
                <div className='text-5xl font-medium'>14</div>
                <User className='text-primary/70' size={40} strokeWidth={1}/>
            </div>

         </Card>
           <Card className='p-6 w-full'>
            <CardTitle className='text-sm font-semibold text-muted-foreground'>Total Devices</CardTitle>
            <div className="flex flex-row items-center justify-between ">
                <div className='text-5xl font-medium'>14</div>
                <Cast className='text-primary/70' size={40} strokeWidth={1}/>
            </div>

         </Card>
       </div>


    </div>
    <Card>1</Card>
  </div>

  <div className='sm:w-[40%]'>

    RIGHT SIDE
  </div>


            </div>
        </AppLayout>
    );
}
