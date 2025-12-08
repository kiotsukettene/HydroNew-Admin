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
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Pagination } from '@/types/pagination';
import { User } from '@/types/user';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import {
    Archive,
    ArrowUpDown,
    Check,
    MoreHorizontal,
    Pencil,
    Users as UsersIcon,
    X,
} from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useDebounce } from 'use-debounce';

export default function Users() {
    const columnsHeader = ['Name', 'Email', 'Address', 'Status', 'Verified'];

    const { users, filters, userCount } = usePage<{
        users: Pagination<User>;
        filters: { search: string };
        userCount: number;
    }>().props;

    const { data, setData, get } = useForm({
        search: filters.search || '',
    });
    const [debounceSearch] = useDebounce(data.search, 500);
    const hasMounted = useRef(false);

    useEffect(() => {
        if (!hasMounted.current) {
            hasMounted.current = true;
            return;
        }

        if (debounceSearch !== undefined) {
            router.get(
                '/users',
                { search: debounceSearch },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }
    }, [debounceSearch]);
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

                <SearchInput
                    value={data.search}
                    onChange={(value) => setData('search', value)}
                    placeholder="Search users..."
                />
                <Table className="border">
                    <TableHeader>
                        <TableRow>
                            {columnsHeader.map((column) => (
                                <TableHead key={column}>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        className="flex items-center gap-1"
                                    >
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
                                                <DropdownMenuItem>
                                                    <Pencil className="mr-2 h-4 w-4" />
                                                    Edit
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    onClick={() =>
                                                        router.visit(
                                                            '/users/archived',
                                                        )
                                                    }
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
            </div>
        </AppLayout>
    );
}
