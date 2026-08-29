import { useEffect, useRef, type ReactNode } from 'react'
import { X } from 'lucide-react'
import { cn } from '@/lib/utils'

interface ModalProps {
  isOpen: boolean
  onClose: () => void
  title?: string
  children: ReactNode
  size?: 'sm' | 'md' | 'lg'
  className?: string
}

const sizeStyles = {
  sm: 'max-w-[400px]',
  md: 'max-w-[480px]',
  lg: 'max-w-[640px]',
}

export function Modal({ isOpen, onClose, title, children, size = 'md', className }: ModalProps) {
  const overlayRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose()
    }
    if (isOpen) {
      document.addEventListener('keydown', handleEscape)
      document.body.style.overflow = 'hidden'
    }
    return () => {
      document.removeEventListener('keydown', handleEscape)
      document.body.style.overflow = ''
    }
  }, [isOpen, onClose])

  if (!isOpen) return null

  return (
    <div
      ref={overlayRef}
      className="fixed inset-0 z-50 flex items-center justify-center p-4"
      onClick={(e) => {
        if (e.target === overlayRef.current) onClose()
      }}
    >
      <div className="fixed inset-0 bg-black/40" />
      <div
        className={cn(
          'relative bg-white rounded-[20px] shadow-elevation-3 w-full p-6 z-10',
          sizeStyles[size],
          className
        )}
      >
        {title && (
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-lg font-semibold text-text-primary">{title}</h2>
            <button
              onClick={onClose}
              className="p-2 rounded-full hover:bg-surface transition-colors"
              aria-label="Close"
            >
              <X className="w-5 h-5 text-text-secondary" />
            </button>
          </div>
        )}
        {children}
      </div>
    </div>
  )
}
