import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
import React, { useEffect, useRef, useState } from 'react'
import SearchInput from '@/components/search-input'
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
import { ArrowUpDown, ArrowUp, ArrowDown, MoreHorizontal, Pencil, Archive, Airplay, Filter, Loader2 } from 'lucide-react'
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

type SortField = 'name' | 'serial_number' | 'status' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function Devices() {
  const { devices, filters, deviceCount } = usePage<{
    devices: Pagination<Device>;
    filters: { search: string; status?: string; sort?: string; direction?: string };
    deviceCount: number;
  }>().props;

  const [selectedDevices, setSelectedDevices] = useState<number[]>([])
  const [isEditOpen, setIsEditOpen] = useState(false)
  const [editingDevice, setEditingDevice] = useState<Device | null>(null)
  const [isSearching, setIsSearching] = useState(false)

  const { data, setData } = useForm({
    search: filters.search || '',
    status: filters.status || 'all',
    sort: (filters.sort as SortField) || 'created_at',
    direction: (filters.direction as SortDirection) || 'desc',
  })

  const [debounceSearch] = useDebounce(data.search, 500)
  const hasMounted = useRef(false)

  // Edit form
  const { data: editData, setData: setEditData, put, processing, reset } = useForm({
    name: '',
    serial_number: '',
    status: '',
  })

  const handleEditClick = (device: Device) => {
    setEditingDevice(device)
    setEditData({
      name: device.name,
      serial_number: device.serial_number,
      status: device.status || '',
    })
    setIsEditOpen(true)
  }

  const handleSaveEdit = () => {
    if (editingDevice) {
      put(`/devices/${editingDevice.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          setIsEditOpen(false)
          reset()
          setEditingDevice(null)
        },
      })
    }
  }

  const handleSort = (field: SortField) => {
    const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';
    
    router.get(
      '/devices',
      { 
        search: data.search, 
        status: data.status,
        sort: field, 
        direction: newDirection
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
      setIsSearching(true)
      router.get(
        '/devices',
        { 
          search: debounceSearch, 
          status: data.status,
          sort: data.sort,
          direction: data.direction
        },
        {
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onFinish: () => setIsSearching(false),
        }
      )
    }
  }, [debounceSearch])

  const handleStatusChange = (status: string) => {
    router.get(
      '/devices',
      { 
        search: data.search, 
        status: status,
        sort: data.sort,
        direction: data.direction
      },
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
                <Card className="bg-orange-100/60  rounded-lg p-4 w-3xs mb-4 border-none">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold">{deviceCount}</span>
                            <Badge className="bg-green-600 px-2 py-0.5 text-xs text-white hover:bg-green-700">
                                Total
                            </Badge>
                        </div>
                        <div className="rounded-md bg-white p-2">
                            <Airplay className="size-8 text-orange-500" />
                        </div>
                    </div>
                    <p className="text-sm text-gray-600 mt-2">Registered devices</p>
                </Card>

        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <SearchInput 
            placeholder="Search by device name, serial number, or owner..." 
            value={data.search}
            onChange={(value) => setData('search', value)}
          />
          <div className="flex gap-3">
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
              <SelectItem value="connected" className="rounded-lg text-xs">
              Connected
            </SelectItem>
            <SelectItem value="not connected" className="rounded-lg text-xs">
              Not Connected
            </SelectItem>
          </SelectContent>
        </Select>
        <Button
          variant="secondary"
          onClick={() => router.visit("/devices/archived")}
        >
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
                    onClick={() => handleSort('name')}
                  >
                    {getSortIcon('name')}
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Owner</Label>
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
                      aria-label={`Select ${device.name}`}
                    />
                  </TableCell>
                  <TableCell className="font-medium ">
                    {device.name}
                  </TableCell>
                  <TableCell>
                    {device.user ? (
                      <div className="flex flex-col">
                        <span className="font-medium text-sm">
                          {device.user.first_name} {device.user.last_name}
                        </span>
                        <span className="text-xs text-muted-foreground">
                          {device.user.email}
                        </span>
                      </div>
                    ) : (
                      <span className="text-muted-foreground text-sm">No owner</span>
                    )}
                  </TableCell>
                  <TableCell className="font-mono text-sm">
                    {device.serial_number}
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <span
                        className={`h-2 w-2 rounded-full ${
                          device.status === 'connected'
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
                        <DropdownMenuItem
                          onClick={() => router.patch(`/devices/${device.id}/archive`, {}, {
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
                  <Label className="text-sm font-medium">Device Name</Label>
                  <input
                    className="w-full rounded-md border border-border bg-background px-3 py-2 text-sm"
                    value={editData.name}
                    onChange={(e) => setEditData('name', e.target.value)}
                  />
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
                    {editData.status === 'connected' ? 'Connected' : 'Not Connected'}
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
                disabled={processing}
              >
                {processing ? 'Saving...' : 'Save changes'}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

      </div>
    </AppLayout>
  )
}
