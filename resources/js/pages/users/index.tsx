import AppLayout from '@/layouts/app-layout';
import PaginationComp from '@/components/pagination';
import SearchInput from '@/components/search-input';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Pagination } from '@/types/pagination';
import { User } from '@/types/user';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Archive,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Check,
    Filter,
    Loader2,
    MoreHorizontal,
    Pencil,
    Users as UsersIcon,
    X,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useDebounce } from 'use-debounce';
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
import { Label } from '@/components/ui/label'
import { Card } from '@/components/ui/card';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

type SortField = 'name' | 'email' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function Users() {
    const columnsHeader = [
        { key: 'name', label: 'Name', sortable: true },
        { key: 'email', label: 'Email', sortable: true },
        { key: 'address', label: 'Address', sortable: false },
        { key: 'status', label: 'Status', sortable: false },
        { key: 'verified', label: 'Verified', sortable: false },
    ];

    const { users, filters, userCount } = usePage<{
        users: Pagination<User>;
        filters: { search: string; sort?: string; direction?: string; status?: string; verified?: string };
        userCount: number;
    }>().props;

    const { data, setData, get } = useForm({
        search: filters.search || '',
        sort: (filters.sort as SortField) || 'created_at',
        direction: (filters.direction as SortDirection) || 'desc',
        status: filters.status || 'all',
        verified: filters.verified || 'all',
    });
    const [debounceSearch] = useDebounce(data.search, 500);
    const [isSearching, setIsSearching] = useState(false);
    const hasMounted = useRef(false);

    // Edit modal state
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [editingUser, setEditingUser] = useState<User | null>(null);

    // Edit form
    const { data: editData, setData: setEditData, put, processing, reset } = useForm({
        first_name: '',
        last_name: '',
        email: '',
        address: '',
    });

    const handleEditClick = (user: User) => {
        setEditingUser(user);
        setEditData({
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            address: user.address || '',
        });
        setIsEditOpen(true);
    };

    const handleSaveEdit = () => {
        if (editingUser) {
            put(`/users/${editingUser.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    setIsEditOpen(false);
                    reset();
                    setEditingUser(null);
                },
            });
        }
    };

    const handleSort = (field: SortField) => {
        const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';
        
        router.get(
            '/users',
            { 
                search: data.search, 
                sort: field, 
                direction: newDirection,
                status: data.status,
                verified: data.verified
            },
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

    const handleFilterChange = (filterType: 'status' | 'verified', value: string) => {
        router.get(
            '/users',
            {
                search: data.search,
                sort: data.sort,
                direction: data.direction,
                status: filterType === 'status' ? value : data.status,
                verified: filterType === 'verified' ? value : data.verified,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onSuccess: () => {
                    setData(filterType, value);
                }
            }
        );
    };

    useEffect(() => {
        if (!hasMounted.current) {
            hasMounted.current = true;
            return;
        }

        if (debounceSearch !== undefined) {
            setIsSearching(true);
            router.get(
                '/users',
                { 
                    search: debounceSearch, 
                    sort: data.sort, 
                    direction: data.direction,
                    status: data.status,
                    verified: data.verified
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onFinish: () => setIsSearching(false),
                },
            );
        }
    }, [debounceSearch]);

    const getSortIcon = (field: string) => {
        if (data.sort !== field) return <ArrowUpDown className="h-4 w-4" />;
        return data.direction === 'asc' 
            ? <ArrowUp className="h-4 w-4" /> 
            : <ArrowDown className="h-4 w-4" />;
    };

    return (
        <AppLayout title="">
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold">Registered Users</h1>
                    <p className="text-muted-foreground">
                        Manage your application's users
                    </p>
                </div>

                {/* Total Users Card */}
                <div className="mb-4 w-fit rounded-lg bg-orange-100/60 p-4">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold">
                                {userCount}
                            </span>
                            <Badge className="bg-green-600 px-2 py-0.5 text-xs text-white hover:bg-green-700">
                                Total
                            </Badge>
                        </div>
                        <div className="rounded-md bg-white p-2">
                            <UsersIcon className="size-8 text-orange-500" />
                        </div>
                    </div>
                    <p className="mt-2 text-sm text-gray-600">
                        Registered users
                    </p>
                </div>

                <div className="flex flex-wrap gap-3 items-center">
                    <SearchInput
                        value={data.search}
                        onChange={(value) => setData('search', value)}
                        placeholder="Search users..."
                    />

                    <Popover>
                        <PopoverTrigger asChild>
                            <Button variant="secondary">
                                <Filter className="mr-2 h-4 w-4" />
                                Filters
                                {(data.status !== 'all' || data.verified !== 'all') && (
                                    <Badge className="ml-2 bg-orange-500 text-white px-1.5 py-0 text-xs">
                                        {[data.status !== 'all' ? 1 : 0, data.verified !== 'all' ? 1 : 0].reduce((a, b) => a + b, 0)}
                                    </Badge>
                                )}
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent className="w-80">
                            <div className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="font-medium leading-none">Filters</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Filter users by status and verification
                                    </p>
                                </div>
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="status-filter" className="text-sm font-medium">
                                            Status
                                        </Label>
                                        <Select value={data.status} onValueChange={(value) => handleFilterChange('status', value)}>
                                            <SelectTrigger id="status-filter">
                                                <SelectValue placeholder="Filter by status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Status</SelectItem>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="inactive">Inactive</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="verified-filter" className="text-sm font-medium">
                                            Verification
                                        </Label>
                                        <Select value={data.verified} onValueChange={(value) => handleFilterChange('verified', value)}>
                                            <SelectTrigger id="verified-filter">
                                                <SelectValue placeholder="Filter by verified" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="all">All Users</SelectItem>
                                                <SelectItem value="verified">Verified</SelectItem>
                                                <SelectItem value="unverified">Unverified</SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    {(data.status !== 'all' || data.verified !== 'all') && (
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            className="w-full"
                                            onClick={() => {
                                                router.get(
                                                    '/users',
                                                    {
                                                        search: data.search,
                                                        sort: data.sort,
                                                        direction: data.direction,
                                                        status: 'all',
                                                        verified: 'all',
                                                    },
                                                    {
                                                        preserveState: true,
                                                        preserveScroll: true,
                                                        replace: true,
                                                        onSuccess: () => {
                                                            setData({ ...data, status: 'all', verified: 'all' });
                                                        }
                                                    }
                                                );
                                            }}
                                        >
                                            Clear Filters
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>

                    <Button 
                        variant="secondary"
                        onClick={() => router.visit('/users/archived')}
                    >
                        <Archive className="mr-2 h-4 w-4" />
                        View Archived
                    </Button>
                </div>
                <Table className="border">
                    <TableHeader>
                        <TableRow>
                            {columnsHeader.map((column) => (
                                <TableHead key={column.key}>
                                    {column.sortable ? (
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            className="flex items-center gap-1"
                                            onClick={() => handleSort(column.key as SortField)}
                                        >
                                            {column.label}
                                            {getSortIcon(column.key)}
                                        </Button>
                                    ) : (
                                        <span className="px-3">{column.label}</span>
                                    )}
                                </TableHead>
                            ))}
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {isSearching ? (
                            <TableRow>
                                <TableCell
                                    colSpan={columnsHeader.length + 1}
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
                                <TableCell
                                    colSpan={columnsHeader.length + 1}
                                    className="h-24 text-center text-gray-500"
                                >
                                    No registered users found.
                                </TableCell>
                            </TableRow>
                        ) : (
                            users.data.map((user) => (
                                <TableRow key={user.email}>
                                    <TableCell className="font-medium">
                                        {user.first_name} {user.last_name}
                                    </TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell className="font-medium">
                                        {user.address}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            className={
                                                user.status === 'active'
                                                    ? 'border-amber-300 bg-amber-100 text-amber-500'
                                                    : 'border-gray-300 bg-gray-100 text-gray-500'
                                            }
                                        >
                                            {user.status
                                                .charAt(0)
                                                .toUpperCase() +
                                                user.status.slice(1)}
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
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                >
                                                    <MoreHorizontal className="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuItem
                                                    onClick={() => handleEditClick(user)}
                                                >
                                                    <Pencil className="mr-2 h-4 w-4" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onClick={() => router.patch(`/users/${user.id}/archive`, {}, {
                                                        preserveScroll: true,
                                                    })}
                                                >
                                                    <Archive className="mr-2 h-4 w-4" />
                                                    Archive
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                {/* Pagination */}
                {users.data.length > 0 && (
                    <div className="flex w-full items-center justify-between gap-2 bg-card px-2 pt-4">
                        <div className="text-sm text-muted-foreground">
                            Showing {users.from || 0} to {users.to || 0} of{' '}
                            {users.total} results
                        </div>
                        <PaginationComp links={users.links} />
                    </div>
                )}

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
              <Label className="text-sm font-medium">First Name</Label>
              <input
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                value={editData.first_name}
                onChange={(e) => setEditData('first_name', e.target.value)}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Last Name</Label>
              <input
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                value={editData.last_name}
                onChange={(e) => setEditData('last_name', e.target.value)}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Email</Label>
              <input
                type="email"
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                value={editData.email}
                onChange={(e) => setEditData('email', e.target.value)}
              />
            </div>
            <div className="grid gap-1">
              <Label className="text-sm font-medium">Address</Label>
              <textarea
                className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                value={editData.address}
                onChange={(e) => setEditData('address', e.target.value)}
                rows={2}
              />
            </div>
          </div>
        )}

        <DialogFooter>
          <Button 
            variant="secondary" 
            onClick={() => {
              setIsEditOpen(false);
              reset();
              setEditingUser(null);
            }}
            disabled={processing}
          >
            Cancel
          </Button>
          <Button 
            onClick={handleSaveEdit}
            disabled={processing}
          >
            {processing ? 'Saving...' : 'Save changes'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
            </div>
        </AppLayout>
    );
}
