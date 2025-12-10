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
import { Label } from '@/components/ui/label'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import SearchInput from '@/components/search-input'
import { Card } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

export default function Users() {

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

  const [editingUser, setEditingUser] = React.useState<(typeof users)[number] | null>(null)
  const [isEditOpen, setIsEditOpen] = React.useState(false)

  const handleEdit = (user: (typeof users)[number]) => {
    setEditingUser(user)
    setIsEditOpen(true)
  }


  return (
     <AppLayout title="">
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className='mb-6'>
        <h1 className="text-2xl font-bold">Registered Users</h1>
        <p className="text-muted-foreground">Manage your application's users</p>
                </div>

                {/* Total Users Card */}
                <Card className="bg-orange-100/60  rounded-lg p-4 w-3xs mb-4 border-none">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold ">{users.length}</span>
                            <Badge className=" text-xs px-2 py-0.5">
                                Total
                            </Badge>
                        </div>

                    </div>
                    <p className="text-sm text-gray-600 mt-2">Registered users</p>
                </Card>

                 <SearchInput placeholder="Search users..." />
                 <Table className='border'>

      <TableHeader>
        <TableRow>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Name</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Name">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Email</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Email">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Contact Number</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Contact Number">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Address</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Address">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Status</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Status">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Verified</Label>
              <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Verified">
                <ArrowUpDown className="h-4 w-4" />
              </Button>
            </div>
          </TableHead>
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
                  <DropdownMenuItem onClick={() => handleEdit(user)}>
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

    {/* Edit User Modal */}
    <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit User</DialogTitle>
          <DialogDescription>
            Update user details here.
          </DialogDescription>
        </DialogHeader>

        {editingUser && (
          <div className="space-y-3">
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Name</Label>
              <input
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                defaultValue={editingUser.name}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Email</Label>
              <input
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                defaultValue={editingUser.email}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Contact Number</Label>
              <input
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                defaultValue={editingUser.contactNumber}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Address</Label>
              <textarea
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                defaultValue={editingUser.address}
                rows={2}
              />
            </div>
          </div>
        )}

        <DialogFooter>
          <Button variant="secondary" onClick={() => setIsEditOpen(false)}>
            Cancel
          </Button>
          <Button onClick={() => setIsEditOpen(false)}>Save changes</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

            </div>
        </AppLayout>
  )
}

