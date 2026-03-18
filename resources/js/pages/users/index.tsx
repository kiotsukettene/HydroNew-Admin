import AppLayout from '@/layouts/app-layout';
import PaginationComp from '@/components/pagination';
import SearchInput from '@/components/search-input';
import { cleanFilters } from '@/lib/filter-helpers';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
    Filter,
    Loader2,
    MoreHorizontal,
    AlertTriangle,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useDebounce } from 'use-debounce';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'sonner';
import { Card } from '@/components/ui/card';

type SortField = 'name' | 'email' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function Users() {
    const formatLastActive = (lastLoginAt: string | null): string => {
        if (!lastLoginAt) return 'Never';
        const d = new Date(lastLoginAt);
        return d.toLocaleDateString(undefined, { dateStyle: 'medium' });
    };

    const { users, filters, userCount, filteredCount } = usePage<{
        users: Pagination<User>;
        filters: { search: string; sort?: string; direction?: string; status?: string; per_page?: number };
        userCount: number;
        filteredCount: number;
    }>().props;

    const { data, setData } = useForm({
        search: filters.search || '',
        sort: (filters.sort as SortField) || 'created_at',
        direction: (filters.direction as SortDirection) || 'desc',
        status: filters.status || 'all',
        verified: 'all',
        per_page: filters.per_page || 10,
    });
    const [debounceSearch] = useDebounce(data.search, 500);
    const [isSearching, setIsSearching] = useState(false);
    const hasMounted = useRef(false);

    const [selectedUsers, setSelectedUsers] = useState<number[]>([]);

    // Archive confirmation modal state
    const [isArchiveConfirmOpen, setIsArchiveConfirmOpen] = useState(false);
    const [userToArchive, setUserToArchive] = useState<User | null>(null);
    const { patch: archivePatch, processing: isArchiving } = useForm({});

    // Bulk archive confirmation modal state
    const [isBulkArchiveConfirmOpen, setIsBulkArchiveConfirmOpen] = useState(false);
    const [isBulkArchiving, setIsBulkArchiving] = useState(false);

    const [isViewArchivedProcessing, setIsViewArchivedProcessing] = useState(false);
    const [isSorting, setIsSorting] = useState(false);
    const [isClearFiltersProcessing, setIsClearFiltersProcessing] = useState(false);
    const [isPaginationProcessing, setIsPaginationProcessing] = useState(false);

    const handlePagination = (url: string) => {
        if (url && !isPaginationProcessing) {
            router.get(url, {}, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setIsPaginationProcessing(true),
                onFinish: () => setIsPaginationProcessing(false),
            });
        }
    };

    /** Archive is only allowed when user is inactive for at least 6 months */
    const canArchive = (user: User): boolean => {
        if (user.status !== 'inactive') return false;
        // If no login history, treat as inactive long enough
        if (!user.last_login_at) return true;
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
        // last_login_at now comes from login_histories.created_at
        return new Date(user.last_login_at) <= sixMonthsAgo;
    };

    const handleArchiveClick = (user: User) => {
        setUserToArchive(user);
        setIsArchiveConfirmOpen(true);
    };

    const handleArchiveConfirm = () => {
        if (isArchiving || !userToArchive) return;
        archivePatch(`/users/${userToArchive.id}/archive`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsArchiveConfirmOpen(false);
                setUserToArchive(null);
                setSelectedUsers([]);
                toast.success('User archived successfully', {
                    description: `${userToArchive.first_name} ${userToArchive.last_name} has been moved to archives.`,
                });
            },
        });
    };

    const getEligibleUsersForArchive = () => {
        return users.data.filter(user =>
            selectedUsers.includes(user.id) && canArchive(user)
        );
    };

    const handleBulkArchive = () => {
        const eligibleUsers = getEligibleUsersForArchive();
        if (eligibleUsers.length === 0) {
            toast.error('No eligible users selected', {
                description: 'Only inactive users (inactive for at least 6 months) can be archived.',
            });
            return;
        }
        setIsBulkArchiveConfirmOpen(true);
    };

    const handleBulkArchiveConfirm = () => {
        if (isBulkArchiving) return;
        const eligibleUserIds = getEligibleUsersForArchive().map(u => u.id);

        router.patch('/users/bulk-archive', { ids: eligibleUserIds }, {
            preserveScroll: true,
            onStart: () => setIsBulkArchiving(true),
            onFinish: () => setIsBulkArchiving(false),
            onSuccess: () => {
                setSelectedUsers([]);
                setIsBulkArchiveConfirmOpen(false);
                toast.success('Users archived successfully', {
                    description: `${eligibleUserIds.length} user(s) have been moved to archives.`,
                });
            },
        });
    };

    const handleSort = (field: SortField) => {
        if (isSorting) return;
        const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';

        router.get(
            '/users',
            cleanFilters({
                search: data.search,
                sort: field,
                direction: newDirection,
                status: data.status,
                verified: data.verified,
                per_page: data.per_page
            }, { sort: 'created_at', direction: 'desc', status: 'all', verified: 'all', per_page: 10 }),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setIsSorting(true),
                onFinish: () => setIsSorting(false),
                onSuccess: () => {
                    setData({ ...data, sort: field, direction: newDirection });
                }
            }
        );
    };

    const handleFilterChange = (filterType: 'status' | 'verified', value: string) => {
        router.get(
            '/users',
            cleanFilters({
                search: data.search,
                sort: data.sort,
                direction: data.direction,
                status: filterType === 'status' ? value : data.status,
                verified: filterType === 'verified' ? value : data.verified,
                per_page: data.per_page
            }, { sort: 'created_at', direction: 'desc', status: 'all', verified: 'all', per_page: 10 }),
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

    const handlePerPageChange = (value: string) => {
        const perPage = parseInt(value);
        router.get(
            '/users',
            cleanFilters({
                search: data.search,
                sort: data.sort,
                direction: data.direction,
                status: data.status,
                verified: data.verified,
                per_page: perPage
            }, { sort: 'created_at', direction: 'desc', status: 'all', verified: 'all', per_page: 10 }),
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

    useEffect(() => {
        if (!hasMounted.current) {
            hasMounted.current = true;
            return;
        }

        if (debounceSearch !== undefined) {
            router.get(
                '/users',
                cleanFilters({
                    search: debounceSearch,
                    sort: data.sort,
                    direction: data.direction,
                    status: data.status,
                    verified: data.verified,
                    per_page: data.per_page
                }, { sort: 'created_at', direction: 'desc', status: 'all', verified: 'all', per_page: 10 }),
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                    onStart: () => setIsSearching(true),
                    onFinish: () => setIsSearching(false),
                },
            );
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps -- only refetch on search change; filters have their own handlers
    }, [debounceSearch]);

    const getSortIcon = (field: string) => {
        if (data.sort !== field) return <ArrowUpDown className="h-4 w-4" />;
        return data.direction === 'asc'
            ? <ArrowUp className="h-4 w-4" />
            : <ArrowDown className="h-4 w-4" />;
    };

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedUsers(users.data.map((user) => user.id));
        } else {
            setSelectedUsers([]);
        }
    };

    const handleSelectUser = (userId: number, checked: boolean) => {
        if (checked) {
            setSelectedUsers([...selectedUsers, userId]);
        } else {
            setSelectedUsers(selectedUsers.filter((id) => id !== userId));
        }
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

          <Card className="rounded-lg p-4 w-3xs mb-4 border">
                            <div className="flex items-center gap-10">
                                <div className="flex items-center gap-2">
                                    <span className="text-3xl font-bold">{filteredCount}</span>
                                    <Badge className="bg-gray-500 px-2 py-0.5 text-xs text-white">
                                        {filteredCount === userCount ? 'Total' : `${filteredCount} of ${userCount}`}
                                    </Badge>
                                </div>

                            </div>
                            <p className="text-sm text-gray-600 mt-2">
                                {filteredCount === userCount ? 'Registered users' : 'Filtered users'}
                            </p>
                        </Card>

                <div className="flex flex-wrap gap-3 items-center justify-between">
                    <div className="flex gap-3 items-center">
                        <SearchInput
                            value={data.search}
                            onChange={(value) => setData('search', value)}
                            placeholder="Search users..."
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
                        {selectedUsers.length > 0 && getEligibleUsersForArchive().length > 0 && (
                            <Button
                                variant="destructive"
                                size="sm"
                                className="w-auto"
                                disabled={isBulkArchiving}
                                onClick={handleBulkArchive}
                            >
                                <Archive className="mr-2 h-4 w-4" />
                                Archive {getEligibleUsersForArchive().length} selected
                            </Button>
                        )}

                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    className="w-auto"
                                    aria-label="Filter by status"
                                >
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
                                            disabled={isClearFiltersProcessing}
                                            onClick={() => {
                                                router.get(
                                                    '/users',
                                                    cleanFilters({
                                                        search: data.search,
                                                        sort: data.sort,
                                                        direction: data.direction,
                                                        status: 'all',
                                                        verified: 'all',
                                                        per_page: data.per_page
                                                    }, { sort: 'created_at', direction: 'desc', status: 'all', verified: 'all', per_page: 10 }),
                                                    {
                                                        preserveState: true,
                                                        preserveScroll: true,
                                                        replace: true,
                                                        onStart: () => setIsClearFiltersProcessing(true),
                                                        onFinish: () => setIsClearFiltersProcessing(false),
                                                        onSuccess: () => {
                                                            setData({ ...data, status: 'all', verified: 'all' });
                                                        }
                                                    }
                                                );
                                            }}
                                        >
                                            {isClearFiltersProcessing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                                            Clear Filters
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </PopoverContent>
                    </Popover>

                    <Button
                        variant="secondary"
                        size="sm"
                        className="w-auto"
                        disabled={isViewArchivedProcessing}
                        onClick={() => {
                            router.visit('/users/archived', {
                                preserveState: false,
                                onStart: () => setIsViewArchivedProcessing(true),
                                onFinish: () => setIsViewArchivedProcessing(false),
                            });
                        }}
                    >
                        {isViewArchivedProcessing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                        <Archive className="mr-2 h-4 w-4" />
                        View Archived
                    </Button>
                    </div>
                </div>
                <Table className="border">
                    <TableHeader>
                        <TableRow>
                            <TableHead className="w-12">
                                <Checkbox
                                    className="border-gray-300"
                                    checked={selectedUsers.length === users.data.length && users.data.length > 0}
                                    onCheckedChange={handleSelectAll}
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
                                        disabled={isSorting}
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
                                        disabled={isSorting}
                                        onClick={() => handleSort('email')}
                                    >
                                        {getSortIcon('email')}
                                    </Button>
                                </div>
                            </TableHead>
                            <TableHead>
                                <div className="flex items-center gap-1">
                                    <Label className="text-sm font-medium">Address</Label>
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
                            <TableHead>
                                <div className="flex items-center gap-1">
                                    <Label className="text-sm font-medium">Last active</Label>
                                </div>
                            </TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {isSearching ? (
                            <TableRow>
                                <TableCell
                                    colSpan={8}
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
                                    colSpan={8}
                                    className="h-24 text-center text-gray-500"
                                >
                                    No users found.
                                </TableCell>
                            </TableRow>
                        ) : (
                            users.data.map((user) => (
                                <TableRow key={user.email}>
                                    <TableCell className="w-12">
                                        <Checkbox
                                            className="border-gray-300"
                                            checked={selectedUsers.includes(user.id)}
                                            onCheckedChange={(checked) =>
                                                handleSelectUser(user.id, checked as boolean)
                                            }
                                            aria-label={`Select ${user.first_name} ${user.last_name}`}
                                        />
                                    </TableCell>
                                    <TableCell className="font-medium">
                                        {user.first_name} {user.last_name}
                                    </TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell className="font-medium">
                                        {user.address}
                                    </TableCell>
                                    <TableCell>
                                        <Badge className={user.status === 'active'
                                            ? "border-green-300 bg-green-100 text-green-600"
                                            : "border-gray-300 bg-gray-100 text-gray-600"}>
                                            {user.status === 'active' ? 'Active' : 'Inactive'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge className={user.email_verified_at
                                            ? "border-green-300 bg-green-100 text-green-600"
                                            : "border-amber-300 bg-amber-100 text-amber-700"}>
                                            {user.email_verified_at ? 'Verified' : 'Unverified'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-muted-foreground text-sm">
                                        {formatLastActive(user.last_login_at)}
                                    </TableCell>
                                    <TableCell>
                                        {canArchive(user) && (
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
                                                        onClick={() => handleArchiveClick(user)}
                                                    >
                                                        <Archive className="mr-2 h-4 w-4" />
                                                        Archive
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
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
                        <PaginationComp
                            links={users.links}
                            onPageChange={handlePagination}
                            processing={isPaginationProcessing}
                        />
                    </div>
                )}

    {/* Archive Confirmation Modal */}
    <Dialog open={isArchiveConfirmOpen} onOpenChange={setIsArchiveConfirmOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-orange-500" />
            Archive User
          </DialogTitle>
          <DialogDescription>
            Are you sure you want to archive this user? They will be moved to the archived users list.
          </DialogDescription>
        </DialogHeader>

        {userToArchive && (
          <div className="rounded-lg border border-border bg-muted p-4">
            <p className="text-sm font-medium text-foreground">
              {userToArchive.first_name} {userToArchive.last_name}
            </p>
            <p className="text-sm text-muted-foreground">{userToArchive.email}</p>
          </div>
        )}

        <DialogFooter>
          <Button
            variant="secondary"
            onClick={() => {
              setIsArchiveConfirmOpen(false);
              setUserToArchive(null);
            }}
            disabled={isArchiving}
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            onClick={handleArchiveConfirm}
            disabled={isArchiving}
          >
            {isArchiving ? 'Archiving...' : 'Archive User'}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    {/* Bulk Archive Confirmation Modal */}
    <Dialog open={isBulkArchiveConfirmOpen} onOpenChange={setIsBulkArchiveConfirmOpen}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-orange-500" />
            Archive Users
          </DialogTitle>
          <DialogDescription>
            Are you sure you want to archive {getEligibleUsersForArchive().length} user(s)? They will be moved to the archived users list.
          </DialogDescription>
        </DialogHeader>

        <div className="rounded-lg border border-border bg-muted p-4">
          <p className="text-sm font-medium text-foreground">
            {getEligibleUsersForArchive().length} eligible user(s) selected
          </p>
          <p className="text-xs text-muted-foreground mt-1">
            Only inactive users (inactive for at least 6 months) will be archived.
          </p>
          {selectedUsers.length > getEligibleUsersForArchive().length && (
            <p className="text-xs text-amber-600 mt-2">
              Note: {selectedUsers.length - getEligibleUsersForArchive().length} user(s) do not meet archive requirements and will be skipped.
            </p>
          )}
        </div>

        <DialogFooter>
          <Button
            variant="secondary"
            onClick={() => {
              setIsBulkArchiveConfirmOpen(false);
            }}
            disabled={isBulkArchiving}
          >
            Cancel
          </Button>
          <Button
            variant="destructive"
            onClick={handleBulkArchiveConfirm}
            disabled={isBulkArchiving}
          >
            {isBulkArchiving ? (
              <>
                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                Archiving...
              </>
            ) : (
              `Archive ${getEligibleUsersForArchive().length} User(s)`
            )}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

            </div>
        </AppLayout>
    );
}
