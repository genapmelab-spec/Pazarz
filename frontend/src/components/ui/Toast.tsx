import { useEffect, useState } from 'react'
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-react'
import { cn } from '@/lib/utils'

type ToastType = 'success' | 'error' | 'warning' | 'info'

interface ToastProps {
  message: string
  type?: ToastType
  onClose: () => void
  duration?: number
}

const typeConfig = {
  success: {
    icon: CheckCircle,
    style: 'bg-success/10 border-success/20 text-success',
  },
  error: {
    icon: AlertCircle,
    style: 'bg-error/10 border-error/20 text-error',
  },
  warning: {
    icon: AlertTriangle,
    style: 'bg-warning/10 border-warning/20 text-warning',
  },
  info: {
    icon: Info,
    style: 'bg-info/10 border-info/20 text-info',
  },
}

export function Toast({ message, type = 'info', onClose, duration = 4000 }: ToastProps) {
  const [isVisible, setIsVisible] = useState(true)
  const config = typeConfig[type]
  const Icon = config.icon

  useEffect(() => {
    const timer = setTimeout(() => {
      setIsVisible(false)
      setTimeout(onClose, 300)
    }, duration)
    return () => clearTimeout(timer)
  }, [duration, onClose])

  return (
    <div
      className={cn(
        'flex items-center gap-3 px-4 py-3 rounded-[12px] border shadow-elevation-2',
        'max-w-[400px] toast-enter',
        config.style,
        !isVisible && 'opacity-0 transition-opacity duration-300'
      )}
    >
      <Icon className="w-5 h-5 flex-shrink-0" />
      <p className="text-sm flex-1">{message}</p>
      <button
        onClick={() => {
          setIsVisible(false)
          setTimeout(onClose, 300)
        }}
        className="p-1 rounded-full hover:bg-white/50 transition-colors"
        aria-label="Dismiss"
      >
        <X className="w-4 h-4" />
      </button>
    </div>
  )
}

interface ToastContainerProps {
  toasts: Array<{ id: string; message: string; type: ToastType }>
  onRemove: (id: string) => void
}

export function ToastContainer({ toasts, onRemove }: ToastContainerProps) {
  return (
    <div className="fixed top-4 right-4 z-[100] flex flex-col gap-2">
      {toasts.map((toast) => (
        <Toast
          key={toast.id}
          message={toast.message}
          type={toast.type}
          onClose={() => onRemove(toast.id)}
        />
      ))}
    </div>
  )
}
