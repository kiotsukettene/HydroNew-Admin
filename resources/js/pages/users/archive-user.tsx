import AppLayout from '@/layouts/app-layout'
import { Head, usePage, router } from '@inertiajs/react'
import React, { useEffect, useState } from 'react'
import { cleanFilters } from '@/lib/filter-helpers'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { ArrowUpDown, MoreHorizontal, RotateCcw, Check, X, ArrowLeft } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import { User } from "@/types/user"
import { Pagination } from "@/types/pagination"
import PaginationComp from "@/components/pagination"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'sonner';

export default function ArchiveUser() {
  const { users, filters } = usePage<{
    users: Pagination<User>
    filters: { search?: string }
  }>().props
  const [search, setSearch] = React.useState(filters.search || "")

  // Unarchive confirmation modal state
  const [isUnarchiveConfirmOpen, setIsUnarchiveConfirmOpen] = useState(false);
  const [userToUnarchive, setUserToUnarchive] = useState<User | null>(null);

  useEffect(() => {
    const timer = setTimeout(() => {
      router.get("/users/archived", cleanFilters({ search }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [search])

  const handleUnarchiveClick = (user: User) => {
    setUserToUnarchive(user);
    setIsUnarchiveConfirmOpen(true);
  };

  const handleUnarchiveConfirm = () => {
    if (userToUnarchive) {
      router.patch(`/users/${userToUnarchive.id}/unarchive`, {}, {
        preserveScroll: true,
        onSuccess: () => {
          setIsUnarchiveConfirmOpen(false);
          setUserToUnarchive(null);
          toast.success('User restored successfully', {
            description: `${userToUnarchive.first_name} ${userToUnarchive.last_name} has been restored from archives.`,
          });
        },
        onError: () => {
          toast.error('Failed to restore user', {
            description: 'Please try again later.',
          });
        },
      });
    }
  };

    const columnsHeader = ['Name', 'Email', 'Status', 'Verified'];


  return (
     <AppLayout title="">
            <Head title="Archived Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className='mb-6 flex items-center justify-between'>
                  <div>
                    <h1 className="text-2xl font-bold">Archived Users</h1>
                    <p className="text-muted-foreground">View and manage archived users</p>
                  </div>
                  <Button
                    variant="secondary"
                    onClick={() => router.visit("/users", { preserveState: false })}
                  >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back to Users
                  </Button>
                </div>
                 <input
                  type="text"
                  placeholder="Search archived users..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full md:w-96 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                />
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
        {users.data.length === 0 ? (
          <TableRow>
            <TableCell colSpan={5} className="text-center text-muted-foreground py-8">
              No archived users found
            </TableCell>
          </TableRow>
        ) : (
          users.data.map((user) => (
            <TableRow key={user.id}>
              <TableCell className="font-medium">{user.first_name} {user.last_name}</TableCell>
              <TableCell>{user.email}</TableCell>
              <TableCell>
                <Badge className={user.status === 'active' 
                    ? "border-green-300 bg-green-100 text-green-600" 
                    : "border-gray-300 bg-gray-100 text-gray-600"}>
                  {user.status === 'active' ? 'Active' : 'Inactive'}
                </Badge>
              </TableCell>
              <TableCell>
                {user.email_verified_at ? (
                  <Badge className="border-green-300 bg-green-100 text-green-600">
                    <Check className="mr-1 h-3 w-3" />
                    Verified
                  </Badge>
                ) : (
                  <Badge className="border-orange-300 bg-orange-100 text-orange-600">
                    <X className="mr-1 h-3 w-3" />
                    Unverified
                  </Badge>
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
                    <DropdownMenuItem onClick={() => handleUnarchiveClick(user)}>
                      <RotateCcw className="mr-2 h-4 w-4" />
                      Restore
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </TableCell>
            </TableRow>
          ))
        )}
      </TableBody>

    </Table>

      {users.data.length > 0 && (
        <div className="mt-4">
          <PaginationComp links={users.links} />
        </div>
      )}

      {/* Unarchive Confirmation Modal */}
      <Dialog open={isUnarchiveConfirmOpen} onOpenChange={setIsUnarchiveConfirmOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <RotateCcw className="h-5 w-5 text-green-500" />
              Restore User
            </DialogTitle>
            <DialogDescription>
              Are you sure you want to restore this user? They will be moved back to the active users list.
            </DialogDescription>
          </DialogHeader>

          {userToUnarchive && (
            <div className="rounded-lg border border-border bg-muted p-4">
              <p className="text-sm font-medium text-foreground">
                {userToUnarchive.first_name} {userToUnarchive.last_name}
              </p>
              <p className="text-sm text-muted-foreground">{userToUnarchive.email}</p>
            </div>
          )}

          <DialogFooter>
            <Button 
              variant="secondary" 
              onClick={() => {
                setIsUnarchiveConfirmOpen(false);
                setUserToUnarchive(null);
              }}
            >
              Cancel
            </Button>
            <Button 
              variant="primary"
              onClick={handleUnarchiveConfirm}
            >
              Restore User
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

            </div>
        </AppLayout>
  )
}

