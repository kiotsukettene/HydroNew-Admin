import { PhTdsChart } from '@/components/ph-tds-chart';
import { Card, CardDescription, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Cast, Leaf, User, Droplet, Calendar, Sprout } from 'lucide-react';
import TextureCard from '@/components/ui/texture-card';
import { index as devicesIndex } from '@/routes/devices';



export default function Dashboard() {
    return (
        <AppLayout title="Dashboard">

            <Head title="Dashboard" />
<div className="w-full h-full flex-1 pl-4 pt-4 pb-4 pr-0 gap-5 flex flex-col sm:flex-row sm:gap-5">
            {/* <div className="text-2xl font-bold text-primary">Dashboard</div> */}


  <div className='sm:w-[70%] flex flex-col gap-4'>
    {/* Welcome Banner + Overview Stats Row */}
    <div className='flex flex-col lg:flex-row gap-4 w-full'>
      {/* Welcome Card - Left Side */}
      <Card className='bg-[#ebfff0d7] shadow-none w-full lg:w-[60%] overflow-hidden p-0 relative min-h-[180px]'>
        {/* Text Content */}
        <div className='px-6 pt-6 pb-4'>
          <CardTitle className='text-2xl  font-semibold leading-tight mt-4'>Welcome to<br/>Dashboard!</CardTitle>
          <CardDescription className=' text-sm mt-2'>Monitor your hydroponics system activity here.</CardDescription>
        </div>

        {/* White Pill Button - Crop Info */}
        <div className='absolute bottom-8 left-6'>
          {/* <Button className=' rounded-full px-4 py-2 flex items-center transition-shadow'>
            <span className='text-sm font-medium '>🥬 Romaine Lettuce</span>
            <span className='text-xs '>• Day 23</span>
          </Button> */}

          <Button size='sm' asChild>
            <Link href={devicesIndex.url()}>View Devices</Link>
          </Button>

        </div>
      </Card>




      {/* Overview Stats - Right Side */}
      <div className='flex flex-col gap-3 w-full lg:w-[35%]'>
        <Card className='p-4 w-full flex-1'>
          <CardTitle className='text-xs font-semibold text-muted-foreground'>Total Users</CardTitle>
          <div className="flex flex-row items-center justify-between mt-2">
            <div className='text-3xl font-medium'>14</div>
            <User className='text-primary/70' size={32} strokeWidth={1}/>
          </div>
        </Card>
        <Card className='p-4 w-full flex-1'>
          <CardTitle className='text-xs font-semibold text-muted-foreground'>Total Harvested Crops</CardTitle>
          <div className="flex flex-row items-center justify-between mt-2">
            <div className='text-3xl font-medium'>23</div>
            <Leaf className='text-primary/70' size={32} strokeWidth={1}/>
          </div>
        </Card>
        <Card className='p-4 w-full flex-1'>
          <CardTitle className='text-xs font-semibold text-muted-foreground'>Total Devices</CardTitle>
          <div className="flex flex-row items-center justify-between mt-2">
            <div className='text-3xl font-medium'>13</div>
            <Cast className='text-primary/70' size={32} strokeWidth={1}/>
          </div>
        </Card>
      </div>
    </div>

        <PhTdsChart className="flex-1"/>




  </div>

  <div className='sm:w-[30%] flex flex-col gap-4 md:-mr-2'>
    {/* Harvest Schedule Section with Overlay */}
    <Card className='bg-[#2C5F5D] shadow-none overflow-hidden p-0 relative flex-1 flex flex-col md:rounded-r-none'>
      <div className='w-full flex-1'>
        <img
          src="/assets/dashboard-bg-1.svg"
          alt="Hydroponics Illustration"
          className='w-full h-full object-cover'
        />
      </div>

      {/* Overlay Content */}
      <div className='absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-[#2C5F5D] via-[#2C5F5D]/95 to-transparent'>
        <CardTitle className='text-xl text-white font-semibold mb-1'>Harvest schedule</CardTitle>
        <CardDescription className='text-white/70 text-xs mb-4'>
          Ask a question of the support question, Manage request, report an issue.
        </CardDescription>

        {/* Harvest Items */}
        <div className='flex flex-col gap-3'>
          {/* Water Tank Level */}
          <div className='flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3 hover:bg-white/15 transition-colors'>
            <div className='w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0'>
              <Droplet className='text-white' size={24} fill="currentColor" />
            </div>
            <div className='flex-1'>
              <div className='text-white font-medium text-sm'>Water Tank Level</div>
              <div className='text-white/70 text-xs mt-1'>
                Shows current water level at 85%
              </div>
            </div>
          </div>

          {/* Current Growth Stage */}
          <div className='flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3 hover:bg-white/15 transition-colors'>
            <div className='w-12 h-12 rounded-lg bg-orange-500/20 flex items-center justify-center flex-shrink-0'>
              <Sprout className='text-white' size={24} />
            </div>
            <div className='flex-1'>
              <div className='text-white font-medium text-sm'>Current Growth Stage</div>
              <div className='text-white/70 text-xs mt-1'>
                Displays "Vegetative" stage
              </div>
            </div>
          </div>

          {/* Estimated Harvest Date */}
          <div className='flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-lg p-3 hover:bg-white/15 transition-colors'>
            <div className='w-12 h-12 rounded-lg bg-green-500/20 flex items-center justify-center flex-shrink-0'>
              <Calendar className='text-white' size={24} />
            </div>
            <div className='flex-1'>
              <div className='text-white font-medium text-sm'>Estimated Harvest Date</div>
              <div className='text-white/70 text-xs mt-1'>
                Next harvest: Feb 12, 2025 • 14 days remaining
              </div>
            </div>
          </div>
        </div>
      </div>
        </Card>

        {/* Water Tank Level Card */}
        {/* <Card className='p-6 bg-blue-50 border-blue-200'>
        <div className="flex flex-row items-center justify-between">
            <div>
            <CardTitle className='text-sm font-semibold text-blue-900/70 mb-1'>
                Water Tank Level
            </CardTitle>
            <div className='text-4xl font-semibold text-blue-900'>85%</div>
            </div>
            <div className='bg-blue-200 p-3 rounded-full'>
            <Droplet className='text-blue-700' size={32} strokeWidth={2} fill="currentColor"/>
            </div>
        </div>
        </Card> */}

        {/* Current Growth Stage Card */}
        {/* <Card className='p-6 bg-orange-50 border-orange-200'>
        <div className="flex flex-row items-center justify-between">
            <div>
            <CardTitle className='text-sm font-semibold text-orange-900/70 mb-1'>
                Growth Stage
            </CardTitle>
            <div className='text-3xl font-semibold text-orange-900'>Vegetative</div>
            </div>
            <div className='bg-orange-200 p-3 rounded-full'>
            <Sprout className='text-orange-700' size={32} strokeWidth={2}/>
            </div>
        </div>
        </Card> */}

        {/* Estimated Harvest Date Card */}
        {/* <Card className='p-6 bg-green-50 border-green-200'>
        <div className="flex flex-row items-center justify-between">
            <div className='flex-1'>
            <CardTitle className='text-sm font-semibold text-green-900/70 mb-1'>
                Next Harvest
            </CardTitle>
            <div className='text-3xl font-semibold text-green-900'>Feb 12, 2025</div>
            <CardDescription className='text-green-700 text-sm mt-1'>
                14 days remaining
            </CardDescription>
            </div>
            <div className='bg-green-200 p-3 rounded-full'>
            <Calendar className='text-green-700' size={32} strokeWidth={2}/>
            </div>
        </div>
        </Card> */}
    </div>


            </div>
        </AppLayout>
    );
}
