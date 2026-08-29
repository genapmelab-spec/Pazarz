import { cn } from '@/lib/utils'
import { getStatusColor, getStatusLabel } from '@/lib/utils'

interface BadgeProps {
  status: string
  className?: string
  size?: 'sm' | 'md'
}

export function Badge({ status, className, size = 'md' }: BadgeProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full font-medium capitalize',
        size === 'sm' ? 'px-2 py-0.5 text-xs h-5' : 'px-3 py-1 text-xs h-6',
        getStatusColor(status),
        className
      )}
    >
      {getStatusLabel(status)}
    </span>
  )
}

interface SimpleBadgeProps {
  children: React.ReactNode
  variant?: 'default' | 'success' | 'warning' | 'error' | 'info'
  size?: 'sm' | 'md'
  className?: string
}

const variantStyles = {
  default: 'bg-surface text-text-secondary',
  success: 'bg-success/10 text-success',
  warning: 'bg-warning/10 text-warning',
  error: 'bg-error/10 text-error',
  info: 'bg-info/10 text-info',
}

export function SimpleBadge({ children, variant = 'default', size = 'md', className }: SimpleBadgeProps) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full font-medium',
        size === 'sm' ? 'px-2 py-0.5 text-xs h-5' : 'px-3 py-1 text-xs h-6',
        variantStyles[variant],
        className
      )}
    >
      {children}
    </span>
  )
}
