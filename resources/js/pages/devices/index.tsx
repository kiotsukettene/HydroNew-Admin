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
import { ArrowUpDown, MoreHorizontal, Pencil, Archive, Airplay } from 'lucide-react'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

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

          {/* Total Users Card */}
                <Card className="bg-orange-100/60  rounded-lg p-4 w-3xs mb-4 border-none">
                    <div className="flex items-center gap-10">
                        <div className="flex items-center gap-2">
                            <span className="text-3xl font-bold ">5</span>
                            <Badge className=" text-xs px-2 py-0.5">
                                Total
                            </Badge>
                        </div>

                    </div>
                    <p className="text-sm text-gray-600 mt-2">Registered users</p>
                </Card>

        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <SearchInput placeholder="Search by date or status..." />
          <Select >
          <SelectTrigger
            className="hidden h-8 w-[150px] border-2 rounded-lg text-xs sm:ml-auto sm:flex"
            aria-label="Select a value"
          >
            <SelectValue placeholder="Select Here" />
          </SelectTrigger>
          <SelectContent className="rounded-xl">
            <SelectItem value="90d" className="rounded-lg text-xs">
              Oldest to Newest
            </SelectItem>
            <SelectItem value="15d" className="rounded-lg text-xs">
              Newest to Oldest
            </SelectItem>


            <SelectItem value="7d" className="rounded-lg text-xs">
              Connected
            </SelectItem>
             <SelectItem value="7d" className="rounded-lg text-xs">
              Not Connected
            </SelectItem>
          </SelectContent>
        </Select>
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
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Device ID</Label>
                  <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Device ID">
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
              </TableHead>
              <TableHead className="text-center">
                <div className="flex items-center justify-center gap-1">
                  <Label className="text-sm font-medium">Connected Users</Label>
                  <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Connected Users">
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
              </TableHead>

              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Serial Number</Label>
                  <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Serial Number">
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Status</Label>
                  <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Status">
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
              </TableHead>
              <TableHead>
                <div className="flex items-center gap-1">
                  <Label className="text-sm font-medium">Date Registered</Label>
                  <Button variant="ghost" size="icon" className="h-8 w-8" aria-label="Sort Date Registered">
                    <ArrowUpDown className="h-4 w-4" />
                  </Button>
                </div>
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
