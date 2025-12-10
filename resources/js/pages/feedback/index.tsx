import AppLayout from '@/layouts/app-layout'
import { Head } from '@inertiajs/react'
import { ChangeEvent, ReactNode, useState } from 'react'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Archive, CheckCircle2, Circle, Mail, Reply, Search, X } from 'lucide-react'
import SearchInput from '@/components/search-input'

type FeedbackStatus = 'unread' | 'replied'

type FeedbackMessage = {
  id: number
  name: string
  email: string
  date: string
  status: FeedbackStatus
  message: string
}

const feedbackMessages: FeedbackMessage[] = [
  {
    id: 1,
    name: 'Ava Thompson',
    email: 'ava.t@example.com',
    date: 'Dec 09, 2025 • 10:14 AM',
    status: 'unread',
    message:
      'I placed an order for nutrient refills yesterday but it is not showing in my history. Can you confirm if it went through?'
  },
  {
    id: 2,
    name: 'Diego Ramirez',
    email: 'diego.ramirez@example.com',
    date: 'Dec 08, 2025 • 3:42 PM',
    status: 'replied',
    message:
      'The automatic lights start a bit late for my region. Is there a way to start them 30 minutes earlier by default?'
  },
  {
    id: 3,
    name: 'Priya Shah',
    email: 'priya.shah@example.com',
    date: 'Dec 08, 2025 • 9:12 AM',
    status: 'unread',
    message:
      'Since the last update, the mobile app signs me out every few hours. I need to log in multiple times a day.'
  },
  {
    id: 5,
    name: 'Mila Novak',
    email: 'mila.novak@example.com',
    date: 'Dec 07, 2025 • 11:28 AM',
    status: 'replied',
    message:
      'The filtration pump makes a rattling sound when it starts. Please advise if this is normal or needs maintenance.'
  },
  {
    id: 6,
    name: 'Ethan Brooks',
    email: 'ethan.brooks@example.com',
    date: 'Dec 06, 2025 • 4:55 PM',
    status: 'unread',
    message:
      'When I try to change the alert thresholds, the save button stays disabled. Is there a permission issue on my account?'
  }
]

const statusStyles: Record<FeedbackStatus, { card: string; pill: string; icon: ReactNode; label: string }> = {
  unread: {
    card: 'bg-muted/60',
    pill: 'bg-indigo-100 text-indigo-700 border-indigo-200',
    icon: <Circle className="h-3 w-3 fill-indigo-500 text-indigo-500" />,
    label: 'Unread'
  },
  replied: {
    card: 'bg-muted/60',
    pill: 'bg-emerald-100 text-emerald-700 border-emerald-200',
    icon: <CheckCircle2 className="h-4 w-4 text-emerald-600" />,
    label: 'Replied'
  }
}

export default function Feedback() {
  const [activeTab, setActiveTab] = useState<'all' | FeedbackStatus>('all')
  const [search, setSearch] = useState('')
  const [selectedMessage, setSelectedMessage] = useState<FeedbackMessage | null>(null)
  const [replyText, setReplyText] = useState('')

  const filteredMessages = feedbackMessages.filter((item) => {
    const term = search.toLowerCase().trim()
    const matchesTab = activeTab === 'all' ? true : item.status === activeTab
    const matchesSearch =
      term.length === 0 ||
      item.name.toLowerCase().includes(term) ||
      item.email.toLowerCase().includes(term) ||
      item.message.toLowerCase().includes(term)
    return matchesTab && matchesSearch
  })

  // Calculate summary counts
  const unreadCount = feedbackMessages.filter((item) => item.status === 'unread').length
  const repliedCount = feedbackMessages.filter((item) => item.status === 'replied').length
  const allMessagesCount = feedbackMessages.length

  const handleSearchChange = (event: ChangeEvent<HTMLInputElement>) => {
    setSearch(event.target.value)
  }

  const handleSelect = (message: FeedbackMessage) => {
    setSelectedMessage(message)
    setReplyText('')
  }

  const handleSendReply = () => {
    // Placeholder for email reply integration
    console.info('Send reply to:', selectedMessage?.email, replyText)
    setSelectedMessage(null)
    setReplyText('')
  }

  return (
    <AppLayout title="">
      <Head title="Feedback" />
      <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">

         <div className="mb-6">
          <h1 className="text-2xl font-bold">Feedback & User Inquiries</h1>
          <p className="text-muted-foreground">
           View and respond to questions submitted from the mobile app.
          </p>
        </div>

        {/* Tabs + Actions */}
        <Tabs
          value={activeTab}
          onValueChange={(value) => setActiveTab(value as 'all' | FeedbackStatus)}
          className="w-full"
        >
          <div className="flex flex-wrap items-center justify-between gap-3">
            <TabsList className="flex h-11 items-center gap-2  bg-muted/60 px-1">
              <TabsTrigger
                value="all"
                className="rounded-full px-4 py-2 text-sm font-medium data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
              >
                All ({allMessagesCount})
              </TabsTrigger>
              <TabsTrigger
                value="unread"
                className="rounded-full px-4 py-2 text-sm font-medium data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
              >
                Unread ({unreadCount})
              </TabsTrigger>
              <TabsTrigger
                value="replied"
                className="rounded-full px-4 py-2 text-sm font-medium data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
              >
                Replied ({repliedCount})
              </TabsTrigger>
            </TabsList>

            <div className="relative">
             <SearchInput placeholder="Search here..." />

            </div>
          </div>
        </Tabs>

        {/* Cards Grid */}
        {filteredMessages.length === 0 ? (
          <div className="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-border bg-muted/40 p-12 text-center">
            <div className="mx-auto flex max-w-lg flex-col items-center gap-3 text-muted-foreground">
              <Mail className="h-10 w-10 text-muted-foreground/70" />
              <p className="text-base font-medium text-foreground">Nothing here yet.</p>
              <p className="text-sm">
                User messages from the mobile app will appear here once submitted.
              </p>
            </div>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {filteredMessages.map((item) => {
              const status = statusStyles[item.status]
              return (
                <Card
                  key={item.id}
                  onClick={() => handleSelect(item)}
                  className={`group h-full cursor-pointer rounded-2xl  p-4 text-left shadow-sm transition hover:-translate-y-1 focus:outline-none ${status.card}`}
                >
                  <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                      <p className="text-sm font-semibold text-foreground">{item.name}</p>
                      <p className="text-xs text-muted-foreground">{item.email}</p>
                    </div>
                    <span
                      className={`inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ${status.pill}`}
                    >
                      {status.icon}
                      {status.label}
                    </span>
                  </div>

                  <div className="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                    <Mail className="h-3.5 w-3.5" />

                    <span>Sent: {item.date}</span>
                  </div>




                </Card>
              )
            })}
          </div>
        )}
      </div>

      {/* Message Detail Modal */}
      {selectedMessage && (() => {
        const message = selectedMessage
        return (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div className="w-full max-w-2xl rounded-2xl border border-border bg-background shadow-2xl">
              <div className="flex items-start justify-between border-b border-border px-6 py-4">
                <div>
                  <p className="text-lg font-semibold text-foreground">{message.name}</p>
                  <p className="text-sm text-muted-foreground">{message.email}</p>
                </div>
                <div className="flex items-center gap-2">
                  <div className={`relative ${statusStyles[message.status]} px-3 py-1.5`} style={{
                    clipPath: 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)'
                  }}>
                    <div className="flex items-center gap-1.5 text-xs font-medium ">
                      {statusStyles[message.status].icon}
                      <span>{statusStyles[message.status].label}</span>
                    </div>
                  </div>
                  <Button
                    variant="icon"
                    size="icon"
                    className="w-auto"
                    onClick={() => setSelectedMessage(null)}
                  >
                    <X className="h-4 w-4" />
                  </Button>
                </div>
              </div>

              <div className="space-y-6 px-6 py-6">
                <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                  <Mail className="h-4 w-4" />
                  <span>{message.date}</span>
                </div>

                <div className="space-y-2 rounded-xl border border-border bg-muted/40 p-4">
                  <p className="text-sm font-medium text-foreground">Message</p>
                  <p className="text-sm text-muted-foreground leading-relaxed">
                    {message.message}
                  </p>
                </div>

                <div className="space-y-2">
                  <label className="text-sm font-medium text-foreground" htmlFor="reply">
                    Reply via email
                  </label>
                  <textarea
                    id="reply"
                    value={replyText}
                    onChange={(e) => setReplyText(e.target.value)}
                    rows={4}
                    className="w-full resize-none rounded-xl border border-border bg-background px-4 py-3 text-sm text-foreground shadow-sm outline-none transition focus:ring-2 focus:ring-primary/30"
                    placeholder="Type your response..."
                  />
                </div>
              </div>

              <div className="flex items-center justify-between border-t border-border px-6 py-4">
                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                  <Reply className="h-3.5 w-3.5" />
                  <span>Reply will be sent to the user&apos;s email</span>
                </div>
                <div className="flex items-center gap-3">
                  <Button
                    variant="secondary"
                    size="sm"
                    className="w-auto"
                    type="button"
                    onClick={() => setSelectedMessage(null)}
                  >
                    Cancel
                  </Button>
                  <Button
                    variant=""
                    size="sm"
                    className="w-auto"
                    type="button"
                    onClick={handleSendReply}
                    disabled={replyText.trim().length === 0}
                  >
                    <CheckCircle2 className="h-4 w-4" />
                    Send Reply
                  </Button>
                </div>
              </div>
            </div>
          </div>
        )
      })()}
    </AppLayout>
  )
}
