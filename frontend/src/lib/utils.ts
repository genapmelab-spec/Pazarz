import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatPrice(amount: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

export function formatDate(date: string): string {
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(date))
}

export function formatDateShort(date: string): string {
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(new Date(date))
}

export function truncate(str: string, length: number): string {
  if (str.length <= length) return str
  return str.slice(0, length) + '...'
}

export function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    pending: 'bg-warning/10 text-warning',
    pending_payment: 'bg-warning/10 text-warning',
    paid: 'bg-info/10 text-info',
    processing: 'bg-warning/10 text-warning',
    confirmed: 'bg-info/10 text-info',
    shipped: 'bg-info/10 text-info',
    delivered: 'bg-success/10 text-success',
    completed: 'bg-success/10 text-success',
    cancelled: 'bg-error/10 text-error',
    rejected: 'bg-error/10 text-error',
    disputed: 'bg-error/10 text-error',
  }
  return colors[status] || 'bg-surface text-text-secondary'
}

export function getStatusLabel(status: string): string {
  const labels: Record<string, string> = {
    pending: 'Menunggu',
    pending_payment: 'Menunggu Pembayaran',
    paid: 'Dibayar',
    processing: 'Diproses',
    confirmed: 'Dikonfirmasi',
    shipped: 'Dikirim',
    delivered: 'Diterima',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
    rejected: 'Ditolak',
    disputed: 'Dispute',
    draft: 'Draft',
    active: 'Aktif',
    inactive: 'Nonaktif',
    archived: 'Diarsipkan',
  }
  return labels[status] || status
}

export function getStarRating(rating: number): string[] {
  const stars: string[] = []
  const full = Math.floor(rating)
  const hasHalf = rating - full >= 0.5
  for (let i = 0; i < 5; i++) {
    if (i < full) stars.push('full')
    else if (i === full && hasHalf) stars.push('half')
    else stars.push('empty')
  }
  return stars
}
