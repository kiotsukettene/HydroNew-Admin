import AppLayout from '@/layouts/app-layout'
import { Head, router, useForm, usePage } from '@inertiajs/react'
import { useEffect, useRef, useState } from 'react'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { CheckCircle2, Loader2, Mail, Reply, X } from 'lucide-react'
import SearchInput from '@/components/search-input'
import { cleanFilters } from '@/lib/filter-helpers'
import { Pagination } from '@/types/pagination'
import { Feedback as FeedbackType, FeedbackCategory } from '@/types/feedback'
import PaginationComp from '@/components/pagination'
import { useDebounce } from 'use-debounce'
import { toast } from 'sonner'

const CATEGORY_LABELS: Record<FeedbackCategory, string> = {
  bug_report: 'Bug report',
  feature_request: 'Feature request',
  general_feedback: 'General feedback',
  device_issue: 'Device issue',
  other: 'Other',
}

const CATEGORY_OPTIONS: { value: 'all' | 'replied' | FeedbackCategory; label: string }[] = [
  { value: 'all', label: 'All' },
  { value: 'replied', label: 'Replied' },
  { value: 'bug_report', label: 'Bug report' },
  { value: 'feature_request', label: 'Feature request' },
  { value: 'general_feedback', label: 'General feedback' },
  { value: 'device_issue', label: 'Device issue' },
  { value: 'other', label: 'Other' },
]

function formatDate(iso: string) {
  return new Date(iso).toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function getUserName(fb: FeedbackType) {
  if (!fb.user) return 'Unknown'
  return [fb.user.first_name, fb.user.last_name].filter(Boolean).join(' ') || fb.user.email
}

export default function Feedback() {
  const { feedback, filters } = usePage<{
    feedback: Pagination<FeedbackType>
    filters: { category?: string; device_id?: string; search?: string }
  }>().props

  const { data, setData } = useForm({
    category: filters.category || 'all',
    search: filters.search || '',
  })

  const [debounceSearch] = useDebounce(data.search, 500)
  const [isSearching, setIsSearching] = useState(false)
  const hasMounted = useRef(false)

  const [selectedFeedback, setSelectedFeedback] = useState<FeedbackType | null>(null)
  const [replyText, setReplyText] = useState('')
  const [isSending, setIsSending] = useState(false)
  const [replyError, setReplyError] = useState('')

  const items = feedback.data
  const total = feedback.total

  useEffect(() => {
    if (!hasMounted.current) {
      hasMounted.current = true
      return
    }
    router.get(
      '/feedback',
      cleanFilters(
        { search: debounceSearch, category: data.category },
        { search: '', category: 'all' }
      ),
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => setIsSearching(true),
        onFinish: () => setIsSearching(false),
      }
    )
  }, [debounceSearch, data.category])

  const handleCategoryChange = (value: string) => {
    setData('category', value)
    router.get(
      '/feedback',
      cleanFilters(
        { search: data.search, category: value },
        { search: '', category: 'all' }
      ),
      { preserveState: true, preserveScroll: true, replace: true }
    )
  }

  const handleSelect = (item: FeedbackType) => {
    setSelectedFeedback(item)
    setReplyText('')
    setReplyError('')
  }

  const handleSendReply = () => {
    if (!selectedFeedback || !selectedFeedback.user?.email || replyText.trim().length === 0) {
      return
    }

    setIsSending(true)
    setReplyError('')

    router.post(
      `/feedback/${selectedFeedback.id}/reply`,
      { reply_message: replyText },
      {
        preserveScroll: true,
        onSuccess: () => {
          setSelectedFeedback(null)
          setReplyText('')
          setReplyError('')
          toast.success('Reply sent successfully!', {
            description: 'Your reply has been sent to the user\'s email.',
          })
        },
        onError: (errors) => {
          console.error('Failed to send reply:', errors)
          
          // Set error message for display below the field
          if (errors.reply_message) {
            setReplyError(errors.reply_message)
          } else if (errors.error) {
            setReplyError(errors.error)
          } else {
            setReplyError('Failed to send reply. Please try again.')
          }
        },
        onFinish: () => {
          setIsSending(false)
        },
      }
    )
  }

  const handlePagination = (url: string) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
  }

  return (
    <AppLayout title="">
      <Head title="Feedback" />
      <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
        <div className="mb-4 sm:mb-6">
          <h1 className="text-xl sm:text-2xl font-bold">Feedback & User Inquiries</h1>
          <p className="text-sm sm:text-base text-muted-foreground">
            View and respond to questions submitted from the mobile app.
          </p>
        </div>

        <Tabs
          value={data.category}
          onValueChange={handleCategoryChange}
          className="w-full"
        >
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <TabsList className="flex h-auto min-h-11 w-full flex-nowrap items-center justify-start gap-2 overflow-x-auto bg-muted/60 px-1 py-1 sm:w-fit sm:flex-wrap sm:justify-center">
              {CATEGORY_OPTIONS.map((opt) => (
                <TabsTrigger
                  key={opt.value}
                  value={opt.value}
                  className="shrink-0 rounded-full px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
                >
                  {opt.value === 'all' || opt.value === 'replied' ? `${opt.label}${data.category === opt.value ? ` (${total})` : ''}` : opt.label}
                </TabsTrigger>
              ))}
            </TabsList>
            <div className="relative w-full sm:w-auto">
              <SearchInput
                placeholder="Search message or subject..."
                value={data.search}
                onChange={(value) => setData('search', value)}
                isLoading={isSearching}
              />
            </div>
          </div>
        </Tabs>

        {items.length === 0 ? (
          <div className="flex flex-1 items-center justify-center rounded-2xl border border-dashed border-border bg-muted/40 p-6 sm:p-12 text-center">
            <div className="mx-auto flex max-w-lg flex-col items-center gap-3 text-muted-foreground">
              <Mail className="h-8 w-8 sm:h-10 sm:w-10 text-muted-foreground/70" />
              <p className="text-sm sm:text-base font-medium text-foreground">Nothing here yet.</p>
              <p className="text-xs sm:text-sm">
                User messages from the mobile app will appear here once submitted.
              </p>
            </div>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
              {items.map((item) => (
                <Card
                  key={item.id}
                  onClick={() => handleSelect(item)}
                  className="group h-full cursor-pointer rounded-2xl p-4 text-left shadow-sm transition hover:-translate-y-1 focus:outline-none bg-muted/60"
                >
                  <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1 min-w-0">
                      <p className="text-sm font-semibold text-foreground truncate">
                        {getUserName(item)}
                      </p>
                      <p className="text-xs text-muted-foreground truncate">
                        {item.user?.email ?? '—'}
                      </p>
                    </div>
                    <Badge variant="secondary" className="shrink-0 text-xs">
                      {CATEGORY_LABELS[item.category]}
                    </Badge>
                  </div>
                  <div className="mt-3 space-y-1">
                    {item.subject && (
                      <p className="text-xs font-medium text-foreground truncate">
                        {item.subject}
                      </p>
                    )}
                    <p className="text-xs text-muted-foreground line-clamp-2">
                      {item.message}
                    </p>
                  </div>
                  <div className="mt-3 flex items-center justify-between gap-2">
                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                      <Mail className="h-3.5 w-3.5 shrink-0" />
                      <span>{formatDate(item.created_at)}</span>
                    </div>
                    {item.replied && (
                      <Badge variant="default" className="text-xs bg-green-500 hover:bg-green-600 shrink-0">
                        Replied
                      </Badge>
                    )}
                  </div>
                </Card>
              ))}
            </div>
            {feedback.links && feedback.links.length > 1 && (
              <PaginationComp
                links={feedback.links}
                onPageChange={handlePagination}
              />
            )}
          </>
        )}
      </div>

      {selectedFeedback && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-2 sm:p-4 backdrop-blur-sm">
          <div className="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-border bg-background shadow-2xl">
            <div className="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-border px-4 sm:px-6 py-4">
              <div className="min-w-0 flex-1">
                <p className="text-base sm:text-lg font-semibold text-foreground truncate">
                  {getUserName(selectedFeedback)}
                </p>
                <p className="text-xs sm:text-sm text-muted-foreground truncate">
                  {selectedFeedback.user?.email ?? '—'}
                </p>
              </div>
              <div className="flex items-center gap-2 shrink-0">
                <Badge variant="secondary" className="text-xs">
                  {CATEGORY_LABELS[selectedFeedback.category]}
                </Badge>
                {selectedFeedback.replied && (
                  <Badge variant="default" className="text-xs bg-green-500 hover:bg-green-600">
                    Replied
                  </Badge>
                )}
                <Button
                  variant="icon"
                  size="icon"
                  className="w-auto"
                  onClick={() => setSelectedFeedback(null)}
                >
                  <X className="h-4 w-4" />
                </Button>
              </div>
            </div>

            <div className="space-y-4 sm:space-y-6 px-4 sm:px-6 py-4 sm:py-6">
              <div className="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-muted-foreground">
                <Mail className="h-3.5 sm:h-4 w-3.5 sm:w-4 shrink-0" />
                <span className="break-words">{formatDate(selectedFeedback.created_at)}</span>
              </div>

              {selectedFeedback.device && (
                <div className="rounded-xl border border-border bg-muted/40 p-3 sm:p-4">
                  <p className="text-xs sm:text-sm font-medium text-foreground">Device</p>
                  <p className="text-xs sm:text-sm text-muted-foreground break-words">
                    {selectedFeedback.device.device_name} ({selectedFeedback.device.serial_number})
                  </p>
                </div>
              )}

              {selectedFeedback.subject && (
                <div className="rounded-xl border border-border bg-muted/40 p-3 sm:p-4">
                  <p className="text-xs sm:text-sm font-medium text-foreground">Subject</p>
                  <p className="text-xs sm:text-sm text-muted-foreground break-words">
                    {selectedFeedback.subject}
                  </p>
                </div>
              )}

              <div className="space-y-2 rounded-xl border border-border bg-muted/40 p-3 sm:p-4">
                <p className="text-xs sm:text-sm font-medium text-foreground">Message</p>
                <p className="text-xs sm:text-sm text-muted-foreground leading-relaxed whitespace-pre-wrap break-words">
                  {selectedFeedback.message}
                </p>
              </div>

              <div className="space-y-2">
                <label className="text-xs sm:text-sm font-medium text-foreground" htmlFor="reply">
                  Reply via email
                </label>
                <textarea
                  id="reply"
                  value={replyText}
                  onChange={(e) => {
                    setReplyText(e.target.value)
                    if (replyError) setReplyError('')
                  }}
                  rows={4}
                  className={`w-full resize-none rounded-xl border bg-background px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm text-foreground shadow-sm outline-none transition focus:ring-2 ${
                    replyError 
                      ? 'border-red-500 focus:ring-red-500/30' 
                      : 'border-border focus:ring-primary/30'
                  }`}
                  placeholder="Type your response..."
                />
                {replyError && (
                  <p className="text-xs sm:text-sm text-red-500 mt-1">
                    {replyError}
                  </p>
                )}
              </div>
            </div>

            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-border px-4 sm:px-6 py-4">
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Reply className="h-3.5 w-3.5 shrink-0" />
                <span className="break-words">Reply will be sent to the user&apos;s email</span>
              </div>
              <div className="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <Button
                  variant="secondary"
                  size="sm"
                  className="flex-1 sm:flex-none sm:w-auto"
                  type="button"
                  onClick={() => setSelectedFeedback(null)}
                >
                  Cancel
                </Button>
                <Button
                  size="sm"
                  className="flex-1 sm:flex-none sm:w-auto"
                  type="button"
                  onClick={handleSendReply}
                  disabled={replyText.trim().length === 0 || isSending}
                >
                  {isSending ? (
                    <>
                      <Loader2 className="h-4 w-4 animate-spin" />
                      Sending...
                    </>
                  ) : (
                    <>
                      <CheckCircle2 className="h-4 w-4" />
                      Send Reply
                    </>
                  )}
                </Button>
              </div>
            </div>
          </div>
        </div>
      )}
    </AppLayout>
  )
}
