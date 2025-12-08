import AppLayout from '@/layouts/app-layout'
import { Head } from '@inertiajs/react'
import React from 'react'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ArrowUpDown, MoreHorizontal, RotateCcw, Trash2 } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import SearchInput from '@/components/search-input'

export default function ArchiveDevices() {
  const columnsHeader = [
    'Device ID',
    'Connected Users',
    'Serial Number',
    'Status',
    'Date Archived',
  ]

  const archivedDevices = [
    {
      id: 1,
      deviceId: 'DEV-007',
      connectedUsers: 0,
      serialNumber: 'SN-PH-20230501',
      status: 'archived',
      dateRegistered: '2023-05-01',
      dateArchived: '2024-12-15',
    },
    {
      id: 2,
      deviceId: 'DEV-008',
      connectedUsers: 0,
      serialNumber: 'SN-WP-20230615',
      status: 'archived',
      dateRegistered: '2023-06-15',
      dateArchived: '2024-11-20',
    },
    {
      id: 3,
      deviceId: 'DEV-009',
      connectedUsers: 0,
      serialNumber: 'SN-TDS-20230720',
      status: 'archived',
      dateRegistered: '2023-07-20',
      dateArchived: '2024-10-10',
    },
  ]

  return (
    <AppLayout title="">
      <Head title="Archived Devices" />
      <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div className="mb-6">
          <h1 className="text-2xl font-bold">Archived Devices</h1>
          <p className="text-muted-foreground">
            View and manage archived devices
          </p>
        </div>

        <SearchInput placeholder="Search archived devices..." />

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
            {archivedDevices.map((device) => (
              <TableRow key={device.id}>
                <TableCell className="font-medium">
                  {device.deviceId}
                </TableCell>
                <TableCell className="text-center">
                  <span className="font-medium text-base">
                    {device.connectedUsers}
                  </span>
                </TableCell>
                <TableCell className="font-mono text-sm">
                  {device.serialNumber}
                </TableCell>
                <TableCell>
                  <span className="inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">
                    Archived
                  </span>
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {new Date(device.dateArchived).toLocaleDateString('en-US', {
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
                      <DropdownMenuItem>
                        <RotateCcw className="mr-2 h-4 w-4" />
                        Restore
                      </DropdownMenuItem>
                      <DropdownMenuItem className="text-red-600">
                        <Trash2 className="mr-2 h-4 w-4" />
                        Delete Permanently
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>
    </AppLayout>
  )
}
