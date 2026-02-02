import AppLayout from '@/layouts/app-layout'
import { BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/react'
import React, { useEffect } from 'react'
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
import { ArrowUpDown, MoreHorizontal, RotateCcw, Trash2, Check, X, ArrowLeft } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import SearchInput from '@/components/search-input'
import { User } from "@/types/user"
import { useForm } from "@inertiajs/react"
import { Pagination } from "@/types/pagination"
import PaginationComp from "@/components/pagination"

export default function ArchiveUser() {
  const { users, filters } = usePage<{
    users: Pagination<User>
    filters: { search?: string }
  }>().props
  const { data, setData, get } = useForm({
    search: filters.search || "",
  })

  useEffect(() => {
    const timer = setTimeout(() => {
      get("/users/archived", {
        preserveState: true,
        preserveScroll: true,
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [data.search])

  const handleUnarchive = (userId: number) => {
    if (confirm("Are you sure you want to restore this user?")) {
      router.patch(`/users/${userId}/unarchive`, {}, {
        preserveState: true,
        preserveScroll: true,
      })
    }
  }

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
                    onClick={() => router.visit("/users")}
                  >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back to Users
                  </Button>
                </div>
                 <input
                  type="text"
                  placeholder="Search archived users..."
                  value={data.search}
                  onChange={(e) => setData("search", e.target.value)}
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
                <Badge
                  className='bg-gray-100 border-gray-300 text-gray-500'
                >
                  {user.status}
                </Badge>
              </TableCell>
              <TableCell>
                {user.email_verified_at ? (
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
                    <DropdownMenuItem onClick={() => handleUnarchive(user.id)}>
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
            </div>
        </AppLayout>
  )
}

