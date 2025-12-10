import AppLayout from '@/layouts/app-layout'
import { Head, router } from '@inertiajs/react'
import React from 'react'
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
import { ArrowUpDown, MoreHorizontal, Pencil, Archive } from 'lucide-react'

// Mock devices data
const devices = [
  {
    id: 1,
    deviceId: 'DEV-001',
    connectedUsers: 3,
    serialNumber: 'SN-PH-20240101',
    status: 'connected',
    dateRegistered: '2025-01-15',
  },
  {
    id: 2,
    deviceId: 'DEV-002',
    connectedUsers: 5,
    serialNumber: 'SN-TDS-20240102',
    status: 'connected',
    dateRegistered: '2025-01-20',
  },
  {
    id: 3,
    deviceId: 'DEV-003',
    connectedUsers: 0,
    serialNumber: 'SN-WP-20240103',
    status: 'not_connected',
    dateRegistered: '2025-02-05',
  },
  {
    id: 4,
    deviceId: 'DEV-004',
    connectedUsers: 2,
    serialNumber: 'SN-TEMP-20240104',
    status: 'connected',
    dateRegistered: '2025-02-10',
  },
  {
    id: 5,
    deviceId: 'DEV-005',
    connectedUsers: 0,
    serialNumber: 'SN-LC-20240105',
    status: 'not_connected',
    dateRegistered: '2025-02-15',
  },
  {
    id: 6,
    deviceId: 'DEV-006',
    connectedUsers: 4,
    serialNumber: 'SN-HUM-20240106',
    status: 'connected',
    dateRegistered: '2025-03-01',
  },
]

export default function Devices() {
  const [selectedDevices, setSelectedDevices] = React.useState<string[]>([])


  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      setSelectedDevices(devices.map((device) => device.deviceId))
    } else {
      setSelectedDevices([])
    }
  }

  const handleSelectDevice = (deviceId: string, checked: boolean) => {
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

        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <SearchInput placeholder="Search devices by serial number, name, or user..." />
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button className="w-fit self-end " >
                Filter
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
              <div className="px-2 py-1.5 text-xs font-semibold text-muted-foreground">Sort</div>
              <DropdownMenuItem>Newest to Oldest</DropdownMenuItem>
              <DropdownMenuItem>Oldest to Newest</DropdownMenuItem>
              <div className="px-2 pt-3 pb-1.5 text-xs font-semibold text-muted-foreground">Status</div>
              <DropdownMenuItem>All</DropdownMenuItem>
              <DropdownMenuItem>Connected</DropdownMenuItem>
              <DropdownMenuItem>Disconnected</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        <Table className="border">
          <TableHeader>
            <TableRow>
              <TableHead className="w-12">
                <Checkbox
                  className="border-gray-300"
                  checked={selectedDevices.length === devices.length && devices.length > 0}
                  onCheckedChange={handleSelectAll}
                  aria-label="Select all"
                />
              </TableHead>
              <TableHead className=''>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center gap-1"
                >
                  Device ID
                  <ArrowUpDown className="h-4 w-4" />
                </Button>
              </TableHead>
              <TableHead className="text-center">
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center gap-1 mx-auto"
                >
                  Connected Users
                  <ArrowUpDown className="h-4 w-4" />
                </Button>
              </TableHead>

              <TableHead>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center gap-1"
                >
                  Serial Number
                  <ArrowUpDown className="h-4 w-4" />
                </Button>
              </TableHead>
              <TableHead>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center gap-1"
                >
                  Status
                  <ArrowUpDown className="h-4 w-4" />
                </Button>
              </TableHead>
              <TableHead>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center gap-1"
                >
                  Date Registered
                  <ArrowUpDown className="h-4 w-4" />
                </Button>
              </TableHead>
              <TableHead></TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            {devices.map((device) => (
              <TableRow key={device.id}>
                <TableCell className="w-12">
                  <Checkbox
                    className="border-gray-300"
                    checked={selectedDevices.includes(device.deviceId)}
                    onCheckedChange={(checked) =>
                      handleSelectDevice(device.deviceId, checked as boolean)
                    }
                    aria-label={`Select ${device.deviceId}`}
                  />
                </TableCell>
                <TableCell className="font-medium ">
                  {device.deviceId}
                </TableCell>
                <TableCell className="text-center">
                  <span className="font-medium text-base">{device.connectedUsers}</span>
                </TableCell>
                <TableCell className="font-mono text-sm">
                  {device.serialNumber}
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
                    <span className="text-sm">
                      {device.status === 'connected'
                        ? 'Connected'
                        : 'Not Connected'}
                    </span>
                  </div>
                </TableCell>
                <TableCell className="text-muted-foreground">
                  {new Date(device.dateRegistered).toLocaleDateString('en-US', {
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
                        <Pencil className="mr-2 h-4 w-4" />
                        Edit
                      </DropdownMenuItem>
                      <DropdownMenuItem
                        onClick={() =>
                          router.visit('/devices/archived')
                        }
                      >
                        <Archive className="mr-2 h-4 w-4" />
                        Archive
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
