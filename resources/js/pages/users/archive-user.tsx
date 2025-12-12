import AppLayout from '@/layouts/app-layout'

import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react'
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
import { ArrowUpDown, MoreHorizontal, RotateCcw, Trash2, Check, X } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import SearchInput from '@/components/search-input'

export default function ArchiveUser() {

    const columnsHeader = ['Name', 'Email', 'Contact Number', 'Address', 'Status', 'Verified'];

    const archivedUsers = [
  {
    first_name: "Michael",
    last_name: "Brown",
    email: "michael.brown@example.com",
    contactNumber: "555-987-6543",
    address: "321 Pine St, Hamletville",
    accountCreated: "2021-08-10",
    archivedDate: "2024-01-15",
    status: "archived",
    verified: true,
  },
  {
    first_name: "Sarah",
    last_name: "Williams",
    email: "sarah.williams@example.com",
    contactNumber: "555-246-8135",
    address: "654 Maple Ave, Countryside",
    accountCreated: "2022-11-05",
    archivedDate: "2024-02-20",
    status: "archived",
    verified: false,
  },
];


  return (
     <AppLayout title="">
            <Head title="Archived Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className='mb-6'>
        <h1 className="text-2xl font-bold">Archived Users</h1>
        <p className="text-muted-foreground">View and manage archived users</p>
                </div>
                 <SearchInput placeholder="Search archived users..." />
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
        {archivedUsers.map((user) => (
          <TableRow key={user.email}>
            <TableCell className="font-medium">{user.first_name} {user.last_name}</TableCell>
            <TableCell>{user.email}</TableCell>
            <TableCell>{user.contactNumber}</TableCell>
            <TableCell className="">{user.address}</TableCell>
            <TableCell>
              <Badge
                className='bg-gray-100 border-gray-300 text-gray-500'
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
                    <RotateCcw className="mr-2 h-4 w-4" />
                    Restore
                  </DropdownMenuItem>
                  <DropdownMenuItem className="text-red-600">
                    <Trash2 className="mr-2 h-4 w-4" />
                    Delete Permanently
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

