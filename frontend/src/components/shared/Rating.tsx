import { cn } from '@/lib/utils'

interface RatingProps {
  value: number
  count?: number
  size?: 'sm' | 'md' | 'lg'
  showCount?: boolean
  className?: string
}

const sizeMap = {
  sm: 'w-3.5 h-3.5',
  md: 'w-4 h-4',
  lg: 'w-5 h-5',
}

export function Rating({ value, count, size = 'sm', showCount = true, className }: RatingProps) {
  return (
    <div className={cn('flex items-center gap-1', className)}>
      {[1, 2, 3, 4, 5].map((star) => (
        <svg
          key={star}
          className={cn(
            sizeMap[size],
            star <= value ? 'text-warning fill-warning' : 'text-border fill-border'
          )}
          viewBox="0 0 20 20"
        >
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
      ))}
      <span className="text-sm text-text-secondary ml-1">{value.toFixed(1)}</span>
      {showCount && count != null && (
        <span className="text-sm text-text-muted">({count})</span>
      )}
    </div>
  )
}

interface StarRatingInputProps {
  value: number
  onChange: (value: number) => void
  size?: 'sm' | 'md' | 'lg'
}

export function StarRatingInput({ value, onChange, size = 'lg' }: StarRatingInputProps) {
  return (
    <div className="flex items-center gap-1">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          onClick={() => onChange(star)}
          className="p-0.5 hover:scale-110 transition-transform"
          aria-label={`${star} star${star > 1 ? 's' : ''}`}
        >
          <svg
            className={cn(
              sizeMap[size],
              'transition-colors',
              star <= value ? 'text-warning fill-warning' : 'text-border fill-border hover:text-warning/50 hover:fill-warning/50'
            )}
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </button>
      ))}
    </div>
  )
}
