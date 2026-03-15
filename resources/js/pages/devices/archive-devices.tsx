import AppLayout from '@/layouts/app-layout'
import { Head, router, usePage, useForm } from '@inertiajs/react'
import React, { useEffect, useState } from 'react'
import { cleanFilters } from '@/lib/filter-helpers'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, MoreHorizontal, RotateCcw, ArrowLeft } from 'lucide-react'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { toast } from 'sonner'

export default function ArchiveDevices() {
  const { devices, filters } = usePage<{
    devices: Pagination<Device>
    filters: { search?: string }
  }>().props
  const [search, setSearch] = React.useState(filters.search || "")
  const [selectedDevices, setSelectedDevices] = useState<number[]>([])

  const [isUnarchiveConfirmOpen, setIsUnarchiveConfirmOpen] = useState(false)
  const [deviceToUnarchive, setDeviceToUnarchive] = useState<Device | null>(null)
  const { patch: unarchivePatch, processing: isRestoring } = useForm({})

  useEffect(() => {
    const timer = setTimeout(() => {
      router.get("/devices/archived", cleanFilters({ search }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [search])

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
    router.patch('/devices/bulk-unarchive', { ids: selectedDevices }, {
      preserveScroll: true,
      onSuccess: () => {
        setSelectedDevices([])
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

  const columnsHeader = [
    'Device Name',
    'Serial Number',
    'Owner',
    'Status',
  ]

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

        <div className="flex flex-wrap gap-3 items-center justify-between">
          <SearchInput
            placeholder="Search archived devices..."
            value={search}
            onChange={(value) => setSearch(value)}
          />
          {selectedDevices.length > 0 && (
            <Button
              variant="default"
              size="sm"
              onClick={handleBulkRestore}
            >
              <RotateCcw className="mr-2 h-4 w-4" />
              Restore {selectedDevices.length} selected
            </Button>
          )}
        </div>

        <Table className="border">
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">
                <Checkbox
                  checked={devices.data.length > 0 && selectedDevices.length === devices.data.length}
                  onCheckedChange={(checked) => handleSelectAll(checked === true)}
                  aria-label="Select all"
                />
              </TableHead>
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
            {devices.data.length === 0 ? (
              <TableRow>
                <TableCell colSpan={6} className="text-center text-muted-foreground py-8">
                  No archived devices found
                </TableCell>
              </TableRow>
            ) : (
              devices.data.map((device) => (
                <TableRow key={device.id}>
                  <TableCell className="w-12">
                    <Checkbox
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
          <div className="mt-4">
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
      </div>
    </AppLayout>
  )
}
