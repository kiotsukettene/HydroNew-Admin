import AppLayout from '@/layouts/app-layout'
import { Head, router, usePage, useForm } from '@inertiajs/react'
import React, { useEffect, useRef, useState } from 'react'
import { cleanFilters } from '@/lib/filter-helpers'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, ArrowUp, ArrowDown, MoreHorizontal, RotateCcw, ArrowLeft, Loader2 } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import SearchInput from '@/components/search-input'
import { Device } from '@/types/device'
import { Pagination } from '@/types/pagination'
import PaginationComp from '@/components/pagination'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'sonner'
import { Card } from '@/components/ui/card'

type SortField = 'device_name' | 'serial_number' | 'status' | 'created_at';
type SortDirection = 'asc' | 'desc';

export default function ArchiveDevices() {
  const { devices, filters, archivedCount, filteredArchivedCount } = usePage<{
    devices: Pagination<Device>
    filters: { search?: string; sort?: string; direction?: string }
    archivedCount: number
    filteredArchivedCount: number
  }>().props

  const { data, setData } = useForm({
    search: filters.search || '',
    sort: (filters?.sort as SortField) || 'created_at',
    direction: (filters?.direction as SortDirection) || 'desc',
  })

  const [selectedDevices, setSelectedDevices] = useState<number[]>([])
  const [isSearching, setIsSearching] = useState(false)
  const [isUnarchiveConfirmOpen, setIsUnarchiveConfirmOpen] = useState(false)
  const [deviceToUnarchive, setDeviceToUnarchive] = useState<Device | null>(null)
  const { patch: unarchivePatch, processing: isRestoring } = useForm({})
  const hasMounted = useRef(false)
  const [isBulkRestoreConfirmOpen, setIsBulkRestoreConfirmOpen] = useState(false)

  useEffect(() => {
    if (!hasMounted.current) {
      hasMounted.current = true;
      return;
    }

    const timer = setTimeout(() => {
      router.get("/devices/archived", cleanFilters({ 
        search: data.search,
        sort: data.sort,
        direction: data.direction
      }, { sort: 'created_at', direction: 'desc' }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => setIsSearching(true),
        onFinish: () => setIsSearching(false),
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [data.search, data.sort, data.direction])

  const handleSort = (field: SortField) => {
    const newDirection = data.sort === field && data.direction === 'asc' ? 'desc' : 'asc';

    router.get(
      '/devices/archived',
      cleanFilters({
        search: data.search,
        sort: field,
        direction: newDirection
      }, { sort: 'created_at', direction: 'desc' }),
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

  const handleUnarchiveClick = (device: Device) => {
    setDeviceToUnarchive(device)
    setIsUnarchiveConfirmOpen(true)
  }

  const handleUnarchiveConfirm = () => {
    if (deviceToUnarchive) {
      unarchivePatch(`/devices/${deviceToUnarchive.id}/unarchive`, {
        preserveScroll: true,
        onSuccess: () => {
          setIsUnarchiveConfirmOpen(false)
          setDeviceToUnarchive(null)
          setSelectedDevices([])
          toast.success('Device restored successfully', {
            description: `${deviceToUnarchive.device_name} has been restored from archives.`,
          })
        },
        onError: (errors) => {
          const message = typeof errors?.error === 'string' ? errors.error : (Array.isArray(errors?.error) ? errors.error[0] : null)
          toast.error('Failed to restore device', {
            description: message || 'Please try again later.',
          })
        },
      })
    }
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

  const handleBulkRestore = () => {
    if (selectedDevices.length === 0) return
    setIsBulkRestoreConfirmOpen(true)
  }

  const handleBulkRestoreConfirm = () => {
    router.patch('/devices/bulk-unarchive', { ids: selectedDevices }, {
      preserveScroll: true,
      onSuccess: () => {
        setSelectedDevices([])
        setIsBulkRestoreConfirmOpen(false)
        toast.success('Devices restored successfully', {
          description: `${selectedDevices.length} device(s) have been restored from archives.`,
        })
      },
      onError: (errors) => {
        const message = typeof errors?.error === 'string' ? errors.error : (Array.isArray(errors?.error) ? errors.error[0] : null)
        toast.error('Failed to restore devices', {
          description: message || 'Please try again later.',
        })
      },
    })
  }

  return (
    <AppLayout title="">
      <Head title="Archived Devices" />

      <div className='p-4'>
         <Button
            size="default"
            className="w-auto"
            onClick={() => router.visit("/devices", { preserveState: false })}
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Devices
          </Button>
      </div>
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold">Archived Devices</h1>
            <p className="text-muted-foreground">
              View and manage archived devices
            </p>
          </div>
         
        </div>

        {/* Archived Devices Count Card */}
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
                {filteredArchivedCount === archivedCount ? 'Archived devices' : 'Filtered archived devices'}
            </p>
        </Card>

        <div className="flex flex-wrap gap-3 items-center justify-between">
          <SearchInput
            placeholder="Search archived devices..."
            value={data.search}
            onChange={(value) => setData('search', value)}
          />
          <div className="flex gap-3">
            {selectedDevices.length > 0 && (
              <Button
                variant="primary"
                size="sm"
                className="w-auto"
                onClick={handleBulkRestore}
              >
                <RotateCcw className="mr-2 h-4 w-4" />
                Restore {selectedDevices.length} selected
              </Button>
            )}
          </div>
        </div>

        <Table className="border">
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">
                <Checkbox
                  className="border-gray-300"
                  checked={devices.data.length > 0 && selectedDevices.length === devices.data.length}
                  onCheckedChange={(checked) => handleSelectAll(checked === true)}
                  aria-label="Select all"
                />
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Device Name</Label>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    aria-label="Sort Device Name"
                    onClick={() => handleSort('device_name')}
                  >
                    {getSortIcon('device_name')}
                  </Button>
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
                  <Label className="text-sm font-medium">Owner</Label>
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
              <TableHead></TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            {isSearching ? (
              <TableRow>
                <TableCell
                  colSpan={6}
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
                <TableCell colSpan={6} className="h-24 text-center text-gray-500">
                  No archived devices found.
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
                        handleSelectDevice(device.id, checked === true)
                      }
                      aria-label={`Select ${device.device_name}`}
                    />
                  </TableCell>
                  <TableCell className="font-medium">
                    {device.device_name}
                  </TableCell>
                  <TableCell className="font-mono text-sm">
                    {device.serial_number}
                  </TableCell>
                  <TableCell>
                    {device.users && device.users.length > 0
                      ? `${device.users[0].first_name} ${device.users[0].last_name}${device.users.length > 1 ? ` +${device.users.length - 1}` : ''}`
                      : 'N/A'}
                  </TableCell>
                  <TableCell>
                    <Badge
                      className={
                        device.status === 'online'
                          ? 'bg-green-100 border-green-300 text-green-700'
                          : 'bg-red-100 border-red-300 text-red-700'
                      }
                    >
                      {device.status === 'online' ? 'Online' : 'Offline'}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="sm">
                          <MoreHorizontal className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => handleUnarchiveClick(device)}>
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

        {devices.data.length > 0 && (
          <div className="flex w-full items-center justify-between gap-2 bg-card px-2 pt-4">
            <div className="text-sm text-muted-foreground">
              Showing {devices.from || 0} to {devices.to || 0} of{' '}
              {devices.total} results
            </div>
            <PaginationComp links={devices.links} />
          </div>
        )}

        {/* Unarchive Confirmation Modal */}
        <Dialog open={isUnarchiveConfirmOpen} onOpenChange={setIsUnarchiveConfirmOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle className="flex items-center gap-2">
                <RotateCcw className="h-5 w-5 text-green-500" />
                Restore Device
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to restore this device? It will be moved back to the active devices list.
              </DialogDescription>
            </DialogHeader>

            {deviceToUnarchive && (
              <div className="rounded-lg border border-border bg-muted p-4">
                <p className="text-sm font-medium text-foreground font-mono">
                  {deviceToUnarchive.device_name}
                </p>
                <p className="text-xs text-muted-foreground font-mono mt-1">
                  {deviceToUnarchive.serial_number}
                </p>
              </div>
            )}

            <DialogFooter>
              <Button
                variant="secondary"
                onClick={() => {
                  setIsUnarchiveConfirmOpen(false)
                  setDeviceToUnarchive(null)
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
                {isRestoring ? 'Restoring...' : 'Restore Device'}
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
                Restore Devices
              </DialogTitle>
              <DialogDescription>
                Are you sure you want to restore {selectedDevices.length} device(s)? They will be moved to the devices list.
              </DialogDescription>
            </DialogHeader>

            <div className="rounded-lg border border-border bg-muted p-4">
              <p className="text-sm font-medium text-foreground">
                {selectedDevices.length} device(s) selected
              </p>
              <p className="text-xs text-muted-foreground mt-1">
                This action will restore all selected devices from the archive.
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
                Restore {selectedDevices.length} Device(s)
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  )
}
