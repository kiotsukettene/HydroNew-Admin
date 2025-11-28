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
import { ArrowUpDown, MoreHorizontal, Pencil, Archive } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

export default function Users() {

    const columnsHeader = ['Name', 'Email', 'Contact Number', 'Address'];

    const users = [
  {
    name: "John Doe",
    email: "john.doe@example.com",
    contactNumber: "123-456-7890",
    address: "123 Main St, Cityville",
    accountCreated: "2022-01-15",
  },
  {
    name: "Jane Smith",
    email: "jane.smith@example.com",
    contactNumber: "987-654-3210",
    address: "456 Elm St, Townsville",
    accountCreated: "2023-03-22",
  },
{    name: "Alice Johnson",
    email: "alice.johnson@example.com",
    contactNumber: "555-123-4567",
    address: "789 Oak St, Villageville",
    accountCreated: "2024-05-10",
  },
];


  return (
     <AppLayout title="Users Management">
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                 <Table className='border'>
      <TableCaption>A list of your recent invoices.</TableCaption>
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
                  <DropdownMenuItem>
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
