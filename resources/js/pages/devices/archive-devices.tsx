import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
import React, { useEffect } from 'react'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, MoreHorizontal, RotateCcw, Trash2, ArrowLeft, Check, X } from 'lucide-react'
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

export default function ArchiveDevices() {
  const { devices, filters } = usePage<{
    devices: Pagination<Device>
    filters: { search?: string }
  }>().props
  const { data, setData, get } = useForm({
    search: filters.search || "",
  })

  useEffect(() => {
    const timer = setTimeout(() => {
      get("/devices/archived", {
        preserveState: true,
        preserveScroll: true,
      })
    }, 500)

    return () => clearTimeout(timer)
  }, [data.search])

  const handleUnarchive = (deviceId: number) => {
    if (confirm("Are you sure you want to restore this device?")) {
      router.patch(`/devices/${deviceId}/unarchive`, {}, {
        preserveState: true,
        preserveScroll: true,
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
            onClick={() => router.visit("/devices")}
          >
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Devices
          </Button>
        </div>

        <input
          type="text"
          placeholder="Search archived devices..."
          value={data.search}
          onChange={(e) => setData("search", e.target.value)}
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
                    {device.name}
                  </TableCell>
                  <TableCell className="font-mono text-sm">
                    {device.serial_number}
                  </TableCell>
                  <TableCell>
                    {device.user ? `${device.user.first_name} ${device.user.last_name}` : 'N/A'}
                  </TableCell>
                  <TableCell>
                    <Badge
                      className={
                        device.status === 'connected'
                          ? 'bg-green-100 border-green-300 text-green-700'
                          : 'bg-red-100 border-red-300 text-red-700'
                      }
                    >
                      {device.status === 'connected' ? 'Connected' : 'Not Connected'}
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
                        <DropdownMenuItem onClick={() => handleUnarchive(device.id)}>
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
      </div>
    </AppLayout>
  )
}
