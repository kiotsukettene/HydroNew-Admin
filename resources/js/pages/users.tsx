import AppLayout from '@/layouts/app-layout'
import users from '@/routes/users';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react'
import React from 'react'

export default function Users() {


  return (
     <AppLayout title="Users Management">
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            </div>
        </AppLayout>
  )
}
