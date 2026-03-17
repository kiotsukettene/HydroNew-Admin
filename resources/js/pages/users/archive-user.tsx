import AppLayout from '@/layouts/app-layout'
import { Head, usePage, router, useForm } from '@inertiajs/react'
import React, { useEffect, useRef, useState } from 'react'
import { cleanFilters } from '@/lib/filter-helpers'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { MoreHorizontal, RotateCcw, Check, X, ArrowLeft, Loader2, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Badge } from '@/components/ui/badge'
import SearchInput from '@/components/search-input'
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
import { Label } from '@/components/ui/label'
import { Card } from '@/components/ui/card';

type SortField = 'name' | 'email' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function ArchiveUser() {
  const { users, filters, archivedCount, filteredArchivedCount } = usePage<{
    users: Pagination<User>
    filters: { search?: string; sort?: string; direction?: string; per_page?: number }
    archivedCount: number
    filteredArchivedCount: number
  }>().props

  const { data, setData } = useForm({
    search: filters?.search || '',
    sort: (filters?.sort as SortField) || 'created_at',
    direction: (filters?.direction as SortDirection) || 'desc',
    per_page: filters?.per_page || 10,
  })

  const [isSearching, setIsSearching] = useState(false)
  const hasMounted = useRef(false)
  const [selectedUsers, setSelectedUsers] = useState<number[]>([])

  // Unarchive confirmation modal state
  const [isUnarchiveConfirmOpen, setIsUnarchiveConfirmOpen] = useState(false);
  const [userToUnarchive, setUserToUnarchive] = useState<User | null>(null);
  const { patch: unarchivePatch, processing: isRestoring } = useForm({});

  // Bulk unarchive confirmation modal state
  const [isBulkRestoreConfirmOpen, setIsBulkRestoreConfirmOpen] = useState(false);

  useEffect(() => {
    if (!hasMounted.current) {
      hasMounted.current = true;
      return;
    }

    const timer = setTimeout(() => {
      router.get("/users/archived", cleanFilters({ 
        search: data.search,
        sort: data.sort,
        direction: data.direction,
        per_page: data.per_page
      }, { sort: 'created_at', direction: 'desc', per_page: 10 }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => setIsSearching(true),
        onFinish: () => setIsSearching(false),
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [data.search])

  const handleSort = (field: SortField) => {
    const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';

    router.get(
      '/users/archived',
      cleanFilters({
        search: data.search,
        sort: field,
        direction: newDirection,
        per_page: data.per_page
      }, { sort: 'created_at', direction: 'desc', per_page: 10 }),
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          setData({ ...data, sort: field, direction: newDirection });
        }
      }
    );
  };

  const handlePerPageChange = (value: string) => {
    const perPage = parseInt(value);
    router.get(
      '/users/archived',
      cleanFilters({
        search: data.search,
        sort: data.sort,
        direction: data.direction,
        per_page: perPage
      }, { sort: 'created_at', direction: 'desc', per_page: 10 }),
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          setData('per_page', perPage);
        }
      }
    );
  };

  const getSortIcon = (field: string) => {
    if (data.sort !== field) return <ArrowUpDown className="h-4 w-4" />;
    return data.direction === 'asc'
      ? <ArrowUp className="h-4 w-4" />
      : <ArrowDown className="h-4 w-4" />;
  };

  const handleUnarchiveClick = (user: User) => {
    setUserToUnarchive(user);
    setIsUnarchiveConfirmOpen(true);
  };

  const handleUnarchiveConfirm = () => {
    if (userToUnarchive) {
      unarchivePatch(`/users/${userToUnarchive.id}/unarchive`, {
        preserveScroll: true,
        onSuccess: () => {
          setIsUnarchiveConfirmOpen(false);
          setUserToUnarchive(null);
          setSelectedUsers([]);
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

  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedUsers(users.data.map((user) => user.id))
    } else {
      setSelectedUsers([])
    }
  }

  const handleSelectUser = (userId: number, checked: boolean) => {
    if (checked) {
      setSelectedUsers([...selectedUsers, userId])
    } else {
      setSelectedUsers(selectedUsers.filter((id) => id !== userId))
    }
  }

  const handleBulkRestore = () => {
    if (selectedUsers.length === 0) return
    setIsBulkRestoreConfirmOpen(true)
  }

  const handleBulkRestoreConfirm = () => {
    router.patch('/users/bulk-unarchive', { ids: selectedUsers }, {
      preserveScroll: true,
      onSuccess: () => {
        setSelectedUsers([])
        setIsBulkRestoreConfirmOpen(false)
        toast.success('Users restored successfully', {
          description: `${selectedUsers.length} user(s) have been restored from archives.`,
        })
      },
      onError: (errors) => {
        const message = typeof errors?.error === 'string' ? errors.error : (Array.isArray(errors?.error) ? errors.error[0] : null)
        toast.error('Failed to restore users', {
          description: message || 'Please try again later.',
        })
      },
    })
  }

  return (
     <AppLayout title="">
            <Head title="Archived Users" />

            <div className='p-4'>
                  <Button
                    size="default"
                    className="w-auto"
                    onClick={() => router.visit("/users", { preserveState: false })}
                  >
                    <ArrowLeft className="mr-2 h-4 w-4" />
                    Back to Users
                  </Button>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

                <div className='mb-6 flex items-center '>
                  <div>
                    <h1 className="text-2xl font-bold">Archived Users</h1>
                    <p className="text-muted-foreground">View and manage archived users</p>
                  </div>
                
                </div>

                {/* Archived Users Count Card */}
                <Card className="rounded-lg p-4 w-3xs mb-4 border">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold">{filteredArchivedCount}</span>
                            <Badge className="bg-gray-500 px-2 py-0.5 text-xs text-white">
                                {filteredArchivedCount === archivedCount ? 'Total' : `${filteredArchivedCount} of ${archivedCount}`}
                            </Badge>
                        </div>
                    </div>
                    <p className="text-sm text-gray-600 mt-2">
                        {filteredArchivedCount === archivedCount ? 'Archived users' : 'Filtered archived users'}
                    </p>
                </Card>

                <div className="flex flex-wrap gap-3 items-center justify-between">
                  <div className="flex gap-3 items-center">
                    <SearchInput
                      placeholder="Search archived users..."
                      value={data.search}
                      onChange={(value) => setData('search', value)}
                    />
                    <div className="flex items-center gap-2">
                      <Label className="text-sm text-muted-foreground whitespace-nowrap">Show</Label>
                      <Select value={data.per_page.toString()} onValueChange={handlePerPageChange}>
                        <SelectTrigger className="w-[70px] h-9">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="10">10</SelectItem>
                          <SelectItem value="25">25</SelectItem>
                          <SelectItem value="50">50</SelectItem>
                          <SelectItem value="100">100</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <div className="flex gap-3">
                    {selectedUsers.length > 0 && (
                      <Button
                        variant="primary"
                        size="sm"
                        className="w-auto"
                        onClick={handleBulkRestore}
                      >
                        <RotateCcw className="mr-2 h-4 w-4" />
                        Restore {selectedUsers.length} selected
                      </Button>
                    )}
                  </div>
                </div>
                 <Table className='border'>

      <TableHeader>
        <TableRow>
          <TableHead className="w-12">
            <Checkbox
              className="border-gray-300"
              checked={users.data.length > 0 && selectedUsers.length === users.data.length}
              onCheckedChange={(checked) => handleSelectAll(checked === true)}
              aria-label="Select all"
            />
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Name</Label>
              <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8"
                aria-label="Sort Name"
                onClick={() => handleSort('name')}
              >
                {getSortIcon('name')}
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Email</Label>
              <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8"
                aria-label="Sort Email"
                onClick={() => handleSort('email')}
              >
                {getSortIcon('email')}
              </Button>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Status</Label>
            </div>
          </TableHead>
          <TableHead>
            <div className="flex items-center gap-1">
              <Label className="text-sm font-medium">Verified</Label>
            </div>
          </TableHead>
          <TableHead></TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        {isSearching && users.data.length === 0 ? (
          <TableRow>
            <TableCell
              colSpan={6}
              className="h-24 text-center"
            >
              <div className="flex items-center justify-center gap-2 text-gray-500">
                <Loader2 className="h-5 w-5 animate-spin" />
                <span>Loading users...</span>
              </div>
            </TableCell>
          </TableRow>
        ) : users.data.length === 0 ? (
          <TableRow>
            <TableCell colSpan={6} className="h-24 text-center text-gray-500">
              No archived users found.
            </TableCell>
          </TableRow>
        ) : (
          users.data.map((user) => (
            <TableRow key={user.id}>
              <TableCell className="w-12">
                <Checkbox
                  className="border-gray-300"
                  checked={selectedUsers.includes(user.id)}
                  onCheckedChange={(checked) =>
                    handleSelectUser(user.id, checked === true)
                  }
                  aria-label={`Select ${user.first_name} ${user.last_name}`}
                />
              </TableCell>
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
        <div className="flex w-full items-center justify-between gap-2 bg-card px-2 pt-4">
          <div className="text-sm text-muted-foreground">
            Showing {users.from || 0} to {users.to || 0} of{' '}
            {users.total} results
          </div>
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
              Are you sure you want to restore this user? They will be moved to the users list.
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
              disabled={isRestoring}
            >
              Cancel
            </Button>
            <Button
              variant="primary"
              onClick={handleUnarchiveConfirm}
              disabled={isRestoring}
            >
              {isRestoring ? 'Restoring...' : 'Restore User'}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Bulk Restore Confirmation Modal */}
      <Dialog open={isBulkRestoreConfirmOpen} onOpenChange={setIsBulkRestoreConfirmOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <RotateCcw className="h-5 w-5 text-green-500" />
              Restore Users
            </DialogTitle>
            <DialogDescription>
              Are you sure you want to restore {selectedUsers.length} user(s)? They will be moved to the users list.
            </DialogDescription>
          </DialogHeader>

          <div className="rounded-lg border border-border bg-muted p-4">
            <p className="text-sm font-medium text-foreground">
              {selectedUsers.length} user(s) selected
            </p>
            <p className="text-xs text-muted-foreground mt-1">
              This action will restore all selected users from the archive.
            </p>
          </div>

          <DialogFooter>
            <Button
              variant="secondary"
              onClick={() => {
                setIsBulkRestoreConfirmOpen(false);
              }}
            >
              Cancel
            </Button>
            <Button
              variant="primary"
              onClick={handleBulkRestoreConfirm}
            >
              Restore {selectedUsers.length} User(s)
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

            </div>
        </AppLayout>
  )
}

