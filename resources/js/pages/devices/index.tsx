import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
import React, { useEffect, useRef, useState } from 'react'
import SearchInput from '@/components/search-input'
import { cleanFilters } from '@/lib/filter-helpers'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, ArrowUp, ArrowDown, MoreHorizontal, Pencil, Archive, Filter, Loader2, AlertTriangle } from 'lucide-react'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Device } from '@/types/device'
import { Pagination } from '@/types/pagination'
import { useDebounce } from 'use-debounce'
import PaginationComp from '@/components/pagination'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'sonner';

type SortField = 'device_name' | 'serial_number' | 'status' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function Devices() {
  const { devices, filters, deviceCount, filteredCount } = usePage<{
    devices: Pagination<Device>;
    filters: { search: string; status?: string; sort?: string; direction?: string; per_page?: number };
    deviceCount: number;
    filteredCount: number;
  }>().props;

  const [selectedDevices, setSelectedDevices] = useState<number[]>([])
  const [isEditOpen, setIsEditOpen] = useState(false)
  const [editingDevice, setEditingDevice] = useState<Device | null>(null)
  const [isCreateOpen, setIsCreateOpen] = useState(false)
  const [isSearching, setIsSearching] = useState(false)
  const [isArchiveConfirmOpen, setIsArchiveConfirmOpen] = useState(false)
  const [deviceToArchive, setDeviceToArchive] = useState<Device | null>(null)
  const { patch: archivePatch, processing: isArchiving } = useForm({})
  const [isBulkArchiveConfirmOpen, setIsBulkArchiveConfirmOpen] = useState(false)
  const [isBulkArchiving, setIsBulkArchiving] = useState(false)
  const [isViewArchivedProcessing, setIsViewArchivedProcessing] = useState(false)
  const [isSorting, setIsSorting] = useState(false)

  const { data, setData } = useForm({
    search: filters.search || '',
    status: filters.status || 'all',
    sort: (filters.sort as SortField) || 'created_at',
    direction: (filters.direction as SortDirection) || 'desc',
    per_page: filters.per_page || 10,
  })

  const [debounceSearch] = useDebounce(data.search, 500)
  const hasMounted = useRef(false)

  // Edit form
  const { data: editData, setData: setEditData, put, processing, reset, errors: editErrors } = useForm({
    device_name: '',
    serial_number: '',
    status: '',
  })

  // Create form
  const { data: createData, setData: setCreateData, post, processing: creating, reset: resetCreate, errors } = useForm({
    device_name: '',
    serial_number: '',
    model: '',
    firmware_version: '',
  })

  const handleEditClick = (device: Device) => {
    setEditingDevice(device)
    setEditData({
      device_name: device.device_name,
      serial_number: device.serial_number,
      status: device.status || '',
    })
    setIsEditOpen(true)
  }

  const hasEditChanges =
    editingDevice !== null &&
    editData.device_name !== (editingDevice?.device_name ?? '')

  const handleSaveEdit = () => {
    if (editingDevice) {
      put(`/devices/${editingDevice.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          setIsEditOpen(false)
          reset()
          setEditingDevice(null)
          toast.success('Device updated successfully', {
            description: `${editData.device_name} has been updated.`,
          })
        },
      })
    }
  }

  /** Archive is only allowed when device is offline and has no active hydroponic setups */
  const canArchive = (device: Device): boolean => {
    if (device.status === 'online') return false
    const activeSetups = device.active_setups_count ?? 0
    return activeSetups === 0
  }

  const handleArchiveClick = (device: Device) => {
    setDeviceToArchive(device)
    setIsArchiveConfirmOpen(true)
  }

  const handleArchiveConfirm = () => {
    if (isArchiving || !deviceToArchive) return;
    archivePatch(`/devices/${deviceToArchive.id}/archive`, {
      preserveScroll: true,
      onSuccess: () => {
        setIsArchiveConfirmOpen(false)
        setDeviceToArchive(null)
        setSelectedDevices([])
        toast.success('Device archived successfully', {
          description: `${deviceToArchive.device_name} has been moved to archives.`,
        })
      },
    })
  }

  const getEligibleDevicesForArchive = () => {
    return devices.data.filter(device => 
      selectedDevices.includes(device.id) && canArchive(device)
    );
  };

  const handleBulkArchive = () => {
    const eligibleDevices = getEligibleDevicesForArchive();
    if (eligibleDevices.length === 0) {
      toast.error('No eligible devices selected', {
        description: 'Only offline devices with no active hydroponic setups can be archived.',
      });
      return;
    }
    setIsBulkArchiveConfirmOpen(true);
  };

  const handleBulkArchiveConfirm = () => {
    if (isBulkArchiving) return;
    const eligibleDeviceIds = getEligibleDevicesForArchive().map(d => d.id);

    router.patch('/devices/bulk-archive', { ids: eligibleDeviceIds }, {
      preserveScroll: true,
      onStart: () => setIsBulkArchiving(true),
      onFinish: () => setIsBulkArchiving(false),
      onSuccess: () => {
        setSelectedDevices([]);
        setIsBulkArchiveConfirmOpen(false);
        toast.success('Devices archived successfully', {
          description: `${eligibleDeviceIds.length} device(s) have been moved to archives.`,
        });
      },
    });
  };

  const handleCreateDevice = () => {
    post('/devices', {
      preserveScroll: true,
      onSuccess: () => {
        setIsCreateOpen(false)
        resetCreate()
        toast.success('Device created successfully', {
          description: `${createData.device_name} has been added to your system.`,
        })
      },
    })
  }

  const handleSort = (field: SortField) => {
    if (isSorting) return;
    const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';

    router.get(
      '/devices',
      cleanFilters({
        search: data.search,
        status: data.status,
        sort: field,
        direction: newDirection,
        per_page: data.per_page
      }, { sort: 'created_at', direction: 'desc', status: 'all', per_page: 10 }),
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

  const getSortIcon = (field: string) => {
    if (data.sort !== field) return <ArrowUpDown className="h-4 w-4" />;
    return data.direction === 'asc'
      ? <ArrowUp className="h-4 w-4" />
      : <ArrowDown className="h-4 w-4" />;
  };

  useEffect(() => {
    if (!hasMounted.current) {
      hasMounted.current = true
      return
    }

    if (debounceSearch !== undefined) {
      router.get(
        '/devices',
        cleanFilters({
          search: debounceSearch,
          status: data.status,
          sort: data.sort,
          direction: data.direction,
          per_page: data.per_page
        }, { sort: 'created_at', direction: 'desc', status: 'all', per_page: 10 }),
        {
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onStart: () => setIsSearching(true),
          onFinish: () => setIsSearching(false),
        }
      )
    }
  }, [debounceSearch])

  const handleStatusChange = (status: string) => {
    router.get(
      '/devices',
      cleanFilters({
        search: data.search,
        status: status,
        sort: data.sort,
        direction: data.direction,
        per_page: data.per_page
      }, { sort: 'created_at', direction: 'desc', status: 'all', per_page: 10 }),
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          setData('status', status);
        }
      }
    )
  }

  const handlePerPageChange = (value: string) => {
    const perPage = parseInt(value);
    router.get(
      '/devices',
      cleanFilters({
        search: data.search,
        status: data.status,
        sort: data.sort,
        direction: data.direction,
        per_page: perPage
      }, { sort: 'created_at', direction: 'desc', status: 'all', per_page: 10 }),
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

  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedDevices(devices.data.map((device) => device.id))
    } else {
      setSelectedDevices([])
    }
  }

  const handleSelectDevice = (deviceId: number, checked: boolean) => {
    if (checked) {
      setSelectedDevices([...selectedDevices, deviceId])
    } else {
      setSelectedDevices(selectedDevices.filter((id) => id !== deviceId))
    }
  }

  return (
    <AppLayout title="">
      <Head title="Devices" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="mb-6">
          <h1 className="text-2xl font-bold">Device Management</h1>
          <p className="text-muted-foreground">
            Manage the connected devices, status, and configuration of your hydroponics setup.
          </p>
        </div>

          {/* Total Devices Card */}
                <Card className="rounded-lg p-4 w-3xs mb-4 border">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold">{filteredCount}</span>
                            <Badge className="bg-gray-500 px-2 py-0.5 text-xs text-white">
                                {filteredCount === deviceCount ? 'Total' : `${filteredCount} of ${deviceCount}`}
                            </Badge>
                        </div>
                        
                    </div>
                    <p className="text-sm text-gray-600 mt-2">
                        {filteredCount === deviceCount ? 'Registered devices' : 'Filtered devices'}
                    </p>
                </Card>

        <div className="flex flex-wrap gap-3 items-center justify-between">
          <div className="flex gap-3 items-center">
            <SearchInput
              placeholder="Search by device name, serial number, or paired user..."
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
            {selectedDevices.length > 0 && getEligibleDevicesForArchive().length > 0 && (
              <Button
                variant="destructive"
                size="sm"
                className="w-auto"
                disabled={isBulkArchiving}
                onClick={handleBulkArchive}
              >
                <Archive className="mr-2 h-4 w-4" />
                Archive {getEligibleDevicesForArchive().length} selected
              </Button>
            )}

            <Select value={data.status} onValueChange={handleStatusChange}>
            <SelectTrigger
              className="hidden h-8 w-[150px] border-2 rounded-lg text-xs sm:ml-auto sm:flex"
              aria-label="Filter by status"
            >
              <Filter className="mr-2 h-4 w-4" />
              <SelectValue placeholder="Filter Status" />
            </SelectTrigger>
            <SelectContent className="rounded-xl">
              <SelectItem value="all" className="rounded-lg text-xs">
                All Devices
              </SelectItem>
              <SelectItem value="online" className="rounded-lg text-xs">
              Online
            </SelectItem>
            <SelectItem value="offline" className="rounded-lg text-xs">
              Offline
            </SelectItem>
          </SelectContent>
        </Select>
        <Button
          size="sm"
          className="w-auto"
          onClick={() => setIsCreateOpen(true)}
        >
          Add Device
        </Button>
        <Button
          variant="secondary"
          size="sm"
          className="w-auto"
          disabled={isViewArchivedProcessing}
          onClick={() => {
            router.visit('/devices/archived', {
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
                  checked={selectedDevices.length === devices.data.length && devices.data.length > 0}
                  onCheckedChange={handleSelectAll}
                  aria-label="Select all"
                />
              </TableHead>
              <TableHead className=''>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Device Name</Label>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    aria-label="Sort Device Name"
                    disabled={isSorting}
                    onClick={() => handleSort('device_name')}
                  >
                    {getSortIcon('device_name')}
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Paired Users</Label>
                </div>
              </TableHead>

              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Serial Number</Label>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    aria-label="Sort Serial Number"
                    disabled={isSorting}
                    onClick={() => handleSort('serial_number')}
                  >
                    {getSortIcon('serial_number')}
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Status</Label>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    aria-label="Sort Status"
                    disabled={isSorting}
                    onClick={() => handleSort('status')}
                  >
                    {getSortIcon('status')}
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Date Registered</Label>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    aria-label="Sort Date Registered"
                    disabled={isSorting}
                    onClick={() => handleSort('created_at')}
                  >
                    {getSortIcon('created_at')}
                  </Button>
                </div>
              </TableHead>
              <TableHead></TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            {isSearching ? (
              <TableRow>
                <TableCell
                  colSpan={7}
                  className="h-24 text-center"
                >
                  <div className="flex items-center justify-center gap-2 text-gray-500">
                    <Loader2 className="h-5 w-5 animate-spin" />
                    <span>Loading devices...</span>
                  </div>
                </TableCell>
              </TableRow>
            ) : devices.data.length === 0 ? (
              <TableRow>
                <TableCell
                  colSpan={7}
                  className="h-24 text-center text-gray-500"
                >
                  No devices found.
                </TableCell>
              </TableRow>
            ) : (
              devices.data.map((device) => (
                <TableRow key={device.id}>
                  <TableCell className="w-12">
                    <Checkbox
                      className="border-gray-300"
                      checked={selectedDevices.includes(device.id)}
                      onCheckedChange={(checked) =>
                        handleSelectDevice(device.id, checked as boolean)
                      }
                      aria-label={`Select ${device.device_name}`}
                    />
                  </TableCell>
                  <TableCell className="font-medium ">
                    {device.device_name}
                  </TableCell>
                  <TableCell>
                    {device.users && device.users.length > 0 ? (
                      <div className="flex flex-col gap-1">
                        {device.users.map((user) => (
                          <div key={user.id} className="flex flex-col">
                            <span className="text-sm font-medium">
                              {user.first_name} {user.last_name}
                            </span>
                            <span className="text-xs text-muted-foreground">
                              {user.email}
                            </span>
                          </div>
                        ))}
                      </div>
                    ) : (
                      <span className="text-muted-foreground text-sm">No paired users</span>
                    )}
                  </TableCell>
                  <TableCell className="font-mono text-sm">
                    {device.serial_number}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <span
                        className={`h-2 w-2 rounded-full ${
                          device.status === 'online'
                            ? 'bg-green-500'
                            : 'bg-gray-400'
                        }`}
                      />
                      <span className="text-sm capitalize">
                        {device.status || 'Unknown'}
                      </span>
                    </div>
                  </TableCell>
                  <TableCell className="text-muted-foreground">
                    {new Date(device.created_at).toLocaleDateString('en-US', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric',
                    })}
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm">
                          <MoreHorizontal className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => handleEditClick(device)}>
                          <Pencil className="mr-2 h-4 w-4" />
                          Edit
                        </DropdownMenuItem>
                        {canArchive(device) && (
                          <DropdownMenuItem onClick={() => handleArchiveClick(device)}>
                            <Archive className="mr-2 h-4 w-4" />
                            Archive
                          </DropdownMenuItem>
                        )}
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>

        {/* Pagination */}
        {devices.data.length > 0 && (
          <div className="flex w-full items-center justify-between gap-2 bg-card px-2 pt-4">
            <div className="text-sm text-muted-foreground">
              Showing {devices.from || 0} to {devices.to || 0} of{' '}
              {devices.total} results
            </div>
            <PaginationComp links={devices.links} />
          </div>
        )}

        {/* Edit Device Modal */}
        <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Edit Device</DialogTitle>
              <DialogDescription>
                Update device details here.
              </DialogDescription>
            </DialogHeader>

            {editingDevice && (
              <div className="space-y-3">
                <div className="grid gap-1">
                  <Label className="text-sm font-medium">Device Name <span className="text-red-500">*</span></Label>
                  <input
                    className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                    value={editData.device_name}
                    onChange={(e) => setEditData('device_name', e.target.value)}
                  />
                  {editErrors.device_name && (
                    <p className="text-xs text-red-500">{editErrors.device_name}</p>
                  )}
                </div>
                <div className="grid gap-1">
                  <Label className="text-sm font-medium">Serial Number</Label>
                  <input
                    className="w-full rounded-md border border-border bg-muted px-3 py-2 text-sm font-mono text-muted-foreground cursor-not-allowed"
                    value={editData.serial_number}
                    disabled
                    readOnly
                  />
                  <p className="text-xs text-muted-foreground">Serial number cannot be changed</p>
                </div>
                <div className="grid gap-1">
                  <Label className="text-sm font-medium">Status</Label>
                  <div className="w-full rounded-md border border-border bg-muted px-3 py-2 text-sm text-muted-foreground">
                    {editData.status === 'online' ? 'Online' : 'Offline'}
                  </div>
                  <p className="text-xs text-muted-foreground">Status is managed by the device automatically</p>
                </div>
              </div>
            )}

            <DialogFooter>
              <Button
                variant="secondary"
                onClick={() => {
                  setIsEditOpen(false)
                  reset()
                  setEditingDevice(null)
                }}
                disabled={processing}
              >
                Cancel
              </Button>
              <Button
                onClick={handleSaveEdit}
                disabled={processing || !hasEditChanges}
              >
                {processing ? 'Saving...' : 'Save changes'}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        {/* Create Device Modal */}
        <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add New Device</DialogTitle>
              <DialogDescription>
                Register a new device to your system.
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-3">
              <div className="grid gap-1">
                <Label className="text-sm font-medium">Device Name <span className="text-red-500">*</span></Label>
                <input
                  className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                  placeholder="Enter device name"
                  value={createData.device_name}
                  onChange={(e) => setCreateData('device_name', e.target.value)}
                />
                {errors.device_name && (
                  <p className="text-xs text-red-500">{errors.device_name}</p>
                )}
              </div>

              <div className="grid gap-1">
                <Label className="text-sm font-medium">Serial Number <span className="text-red-500">*</span></Label>
                <input
                  className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm font-mono"
                  placeholder="Enter serial number"
                  value={createData.serial_number}
                  onChange={(e) => setCreateData('serial_number', e.target.value)}
                />
                {errors.serial_number && (
                  <p className="text-xs text-red-500">{errors.serial_number}</p>
                )}
              </div>

              <div className="grid gap-1">
                <Label className="text-sm font-medium">Model</Label>
                <input
                  className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                  placeholder="Enter model (optional)"
                  value={createData.model}
                  onChange={(e) => setCreateData('model', e.target.value)}
                />
              </div>

              <div className="grid gap-1">
                <Label className="text-sm font-medium">Firmware Version</Label>
                <input
                  className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                  placeholder="Enter firmware version (optional)"
                  value={createData.firmware_version}
                  onChange={(e) => setCreateData('firmware_version', e.target.value)}
                />
              </div>
            </div>

            <DialogFooter>
              <Button
                variant="secondary"
                onClick={() => {
                  setIsCreateOpen(false)
                  resetCreate()
                }}
                disabled={creating}
              >
                Cancel
              </Button>
              <Button
                onClick={handleCreateDevice}
                disabled={creating}
              >
                {creating ? 'Creating...' : 'Create Device'}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        {/* Archive Confirmation Modal */}
        <Dialog open={isArchiveConfirmOpen} onOpenChange={setIsArchiveConfirmOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <AlertTriangle className="h-5 w-5 text-orange-500" />
                Archive Device
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to archive this device? It will be moved to the archived devices list.
              </DialogDescription>
            </DialogHeader>

            {deviceToArchive && (
              <div className="rounded-lg border border-border bg-muted p-4">
                <p className="text-sm font-medium text-foreground">
                  {deviceToArchive.device_name}
                </p>
                <p className="text-sm text-muted-foreground font-mono">{deviceToArchive.serial_number}</p>
              </div>
            )}

            <DialogFooter>
              <Button
                variant="secondary"
                onClick={() => {
                  setIsArchiveConfirmOpen(false)
                  setDeviceToArchive(null)
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
                {isArchiving ? 'Archiving...' : 'Archive Device'}
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
                Archive Devices
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to archive {getEligibleDevicesForArchive().length} device(s)? They will be moved to the archived devices list.
              </DialogDescription>
            </DialogHeader>

            <div className="rounded-lg border border-border bg-muted p-4">
              <p className="text-sm font-medium text-foreground">
                {getEligibleDevicesForArchive().length} eligible device(s) selected
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                Only offline devices with no active hydroponic setups will be archived.
              </p>
              {selectedDevices.length > getEligibleDevicesForArchive().length && (
                <p className="text-xs text-amber-600 mt-2">
                  Note: {selectedDevices.length - getEligibleDevicesForArchive().length} device(s) do not meet archive requirements and will be skipped.
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
                  `Archive ${getEligibleDevicesForArchive().length} Device(s)`
                )}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

      </div>
    </AppLayout>
  )
}

