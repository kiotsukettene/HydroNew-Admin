export type FeedbackCategory =
  | 'bug_report'
  | 'feature_request'
  | 'general_feedback'
  | 'device_issue'
  | 'other'

export interface FeedbackUser {
  id: number
  first_name: string
  last_name: string
  email: string
}

export interface FeedbackDevice {
  id: number
  device_name: string
  serial_number: string
}

export interface Feedback {
  id: number
  user_id: number
  device_id: number
  category: FeedbackCategory
  subject: string | null
  message: string
  replied: boolean
  created_at: string
  updated_at: string
  user?: FeedbackUser
  device?: FeedbackDevice
}
