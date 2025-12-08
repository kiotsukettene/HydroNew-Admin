import AppLayout from '@/layouts/app-layout'

import { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react'
import React from 'react'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { ArrowUpDown, MoreHorizontal, Pencil, Archive, Check, X, Users as UsersIcon } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import SearchInput from '@/components/search-input'

export default function Users() {

    const columnsHeader = ['Name', 'Email', 'Contact Number', 'Address', 'Status', 'Verified'];

    const users = [
  {
    name: "John Doe",
    email: "john.doe@example.com",
    contactNumber: "123-456-7890",
    address: "123 Main St, Cityville",
    accountCreated: "2022-01-15",
    status: "active",
    verified: true,
  },
  {
    name: "Jane Smith",
    email: "jane.smith@example.com",
    contactNumber: "987-654-3210",
    address: "456 Elm St, Townsville",
    accountCreated: "2023-03-22",
    status: "inactive",
    verified: false,
  },
{    name: "Alice Johnson",
    email: "alice.johnson@example.com",
    contactNumber: "555-123-4567",
    address: "789 Oak St, Villageville",
    accountCreated: "2024-05-10",
    status: "active",
    verified: true,
  },
];


  return (
     <AppLayout title="">
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className='mb-6'>
        <h1 className="text-2xl font-bold">Registered Users</h1>
        <p className="text-muted-foreground">Manage your application's users</p>
                </div>

                {/* Total Users Card */}
                <div className="bg-orange-100/60  rounded-lg p-4 w-fit mb-4">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold ">{users.length}</span>
                            <Badge className="bg-green-600 text-white hover:bg-green-700 text-xs px-2 py-0.5">
                                Total
                            </Badge>
                        </div>
                        <div className="bg-white rounded-md p-2">
                            <UsersIcon className="size-8 text-orange-500" />
                        </div>
                    </div>
                    <p className="text-sm text-gray-600 mt-2">Registered users</p>
                </div>

                 <SearchInput placeholder="Search users..." />
                 <Table className='border'>

      <TableHeader>
        <TableRow>
          {columnsHeader.map((column) => (
            <TableHead key={column}>
              <Button variant="ghost" size="sm" className="flex items-center gap-1">
                {column}
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </TableHead>
          ))}
          <TableHead></TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        {users.map((user) => (
          <TableRow key={user.email}>
            <TableCell className="font-medium">{user.name}</TableCell>
            <TableCell>{user.email}</TableCell>
            <TableCell>{user.contactNumber}</TableCell>
            <TableCell className="">{user.address}</TableCell>
            <TableCell>
              <Badge
                className={user.status === 'active'
                  ? 'bg-amber-100 border-amber-300 text-amber-500 '
                  : 'bg-gray-100 border-gray-300 text-gray-500 '
                }
              >
                {user.status.charAt(0).toUpperCase() + user.status.slice(1)}
              </Badge>
            </TableCell>
            <TableCell>
              {user.verified ? (
                <Check className="h-5 w-5 text-green-600" />
              ) : (
                <X className="h-5 w-5 text-red-500" />
              )}
            </TableCell>
            <TableCell>
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm">
                    <MoreHorizontal className="h-4 w-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem>
                    <Pencil className="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => router.visit('/users/archived')}>
                    <Archive className="mr-2 h-4 w-4" />
                    Archive
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>

    </Table>

            </div>
        </AppLayout>
  )
}

