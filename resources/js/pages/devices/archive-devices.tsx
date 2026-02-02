import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
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
import { toast } from 'sonner';

export default function ArchiveDevices() {
  const { devices, filters } = usePage<{
    devices: Pagination<Device>
    filters: { search?: string }
  }>().props
  const [search, setSearch] = React.useState(filters.search || "")

  const [isUnarchiveConfirmOpen, setIsUnarchiveConfirmOpen] = useState(false)
  const [deviceToUnarchive, setDeviceToUnarchive] = useState<Device | null>(null)

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
      router.patch(`/devices/${deviceToUnarchive.id}/unarchive`, {}, {
        preserveScroll: true,
        onSuccess: () => {
          setIsUnarchiveConfirmOpen(false)
          setDeviceToUnarchive(null)
          toast.success('Device restored successfully', {
            description: `${deviceToUnarchive.device_name} has been restored from archives.`,
          })
        },
        onError: () => {
          toast.error('Failed to restore device', {
            description: 'Please try again later.',
          })
        },
      })
    }
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
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold">Archived Devices</h1>
            <p className="text-muted-foreground">
              View and manage archived devices
            </p>
          </div>
          <Button
            variant="secondary"
            onClick={() => router.visit("/devices", { preserveState: false })}
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Devices
          </Button>
        </div>

        <input
          type="text"
          placeholder="Search archived devices..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="w-full md:w-96 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
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
            {devices.data.length === 0 ? (
              <TableRow>
                <TableCell colSpan={5} className="text-center text-muted-foreground py-8">
                  No archived devices found
                </TableCell>
              </TableRow>
            ) : (
              devices.data.map((device) => (
                <TableRow key={device.id}>
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
              >
                Cancel
              </Button>
              <Button 
                variant="primary"
                onClick={handleUnarchiveConfirm}
              >
                Restore Device
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </AppLayout>
  )
}
