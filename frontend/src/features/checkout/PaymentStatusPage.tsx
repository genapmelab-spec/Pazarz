import { useCallback, useEffect, useRef, useState } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { CheckCircle, Clock, XCircle, ArrowRight, RefreshCw } from 'lucide-react'
import api from '@/lib/api'
import { openPayment } from '@/lib/midtrans'
import { Button } from '@/components/ui/Button'
import { formatPrice } from '@/lib/utils'

interface PaymentState {
  order_number: string
  order_status: string
  payment_status: string
  payment_method?: string
  amount?: string | number
  paid_at?: string | null
  retryable: boolean
}

export function PaymentStatusPage() {
  const [searchParams] = useSearchParams()
  const orderNumber = searchParams.get('order')

  const [order, setOrder] = useState<any>(null)
  const [state, setState] = useState<PaymentState | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [isOpening, setIsOpening] = useState(false)
  const [error, setError] = useState('')

  // Guards Snap from auto-opening twice (React StrictMode runs effects twice).
  const autoOpened = useRef(false)

  const fetchStatus = useCallback(async (): Promise<PaymentState | null> => {
    if (!orderNumber) return null

    const [orderRes, statusRes] = await Promise.all([
      api.get(`/orders/${orderNumber}`),
      api.get(`/orders/${orderNumber}/payment-status`),
    ])

    setOrder(orderRes.data.data)
    setState(statusRes.data.data)

    return statusRes.data.data as PaymentState
  }, [orderNumber])

  const handlePay = useCallback(async () => {
    if (!orderNumber) return
    setIsOpening(true)
    setError('')

    try {
      await openPayment(orderNumber, {
        onSuccess: () => { fetchStatus().catch(() => {}) },
        onPending: () => { fetchStatus().catch(() => {}) },
        onError: () => { fetchStatus().catch(() => {}) },
        onClose: () => { fetchStatus().catch(() => {}) },
      })
    } catch (err: any) {
      setError(
        err?.response?.data?.error?.message ||
        err?.message ||
        'Gagal membuka pembayaran Midtrans. Coba lagi.'
      )
    } finally {
      setIsOpening(false)
    }
  }, [orderNumber, fetchStatus])

  // Initial load + auto-open Snap for an order that still awaits payment.
  useEffect(() => {
    if (!orderNumber) {
      setIsLoading(false)
      return
    }

    let cancelled = false

    ;(async () => {
      try {
        const status = await fetchStatus()
        if (!cancelled && status?.order_status === 'pending_payment' && status.retryable && !autoOpened.current) {
          autoOpened.current = true
          await handlePay()
        }
      } catch (err) {
        console.error('Failed to fetch payment status:', err)
      } finally {
        if (!cancelled) setIsLoading(false)
      }
    })()

    return () => { cancelled = true }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [orderNumber])

  // Poll server-verified status while the order is still awaiting payment.
  useEffect(() => {
    if (!orderNumber || !state || state.order_status !== 'pending_payment') return

    const id = setInterval(() => { fetchStatus().catch(() => {}) }, 4000)
    return () => clearInterval(id)
  }, [orderNumber, state?.order_status, fetchStatus])

  if (isLoading) {
    return (
      <div className="max-w-[480px] mx-auto px-5 py-20 text-center">
        <div className="w-16 h-16 mx-auto skeleton rounded-full" />
      </div>
    )
  }

  if (!order) {
    return (
      <div className="max-w-[480px] mx-auto px-5 py-20 text-center">
        <h1 className="text-2xl font-semibold mb-2">Pesanan tidak ditemukan</h1>
        <Link to="/account/orders">
          <Button>Lihat Pesanan</Button>
        </Link>
      </div>
    )
  }

  const status = state?.order_status || order.status || 'pending_payment'
  const isPaid = ['paid', 'processing', 'shipped', 'completed'].includes(status)
  const isPending = status === 'pending_payment'

  return (
    <div className="max-w-[480px] mx-auto px-5 py-16 text-center">
      {isPaid ? (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-success/10 flex items-center justify-center">
            <CheckCircle className="w-10 h-10 text-success" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Pembayaran Berhasil!</h1>
          <p className="text-text-secondary mb-1">Pesanan Anda sedang diproses penjual.</p>
          <p className="text-sm text-text-muted mb-8">Nomor pesanan: {order.order_number}</p>
        </>
      ) : isPending ? (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-warning/10 flex items-center justify-center">
            <Clock className="w-10 h-10 text-warning" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Menunggu Pembayaran</h1>
          <p className="text-text-secondary mb-1">Selesaikan pembayaran melalui Midtrans.</p>
          <p className="text-sm text-text-muted mb-8">Nomor pesanan: {order.order_number}</p>
        </>
      ) : (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-error/10 flex items-center justify-center">
            <XCircle className="w-10 h-10 text-error" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">
            {state?.payment_status === 'expired' ? 'Pembayaran Kedaluwarsa' : 'Pembayaran Gagal'}
          </h1>
          <p className="text-text-secondary mb-2">Terjadi kesalahan saat memproses pembayaran.</p>
          <p className="text-sm text-text-muted mb-8">Nomor pesanan: {order.order_number}</p>
        </>
      )}

      {isPending && (
        <div className="mb-8">
          <div className="rounded-[12px] border border-divider p-4 mb-4 text-sm">
            <div className="flex justify-between">
              <span className="text-text-secondary">Total tagihan</span>
              <span className="font-semibold">{formatPrice(Number(state?.amount ?? order.grand_total))}</span>
            </div>
          </div>
          <Button onClick={handlePay} isLoading={isOpening} className="w-full" size="lg">
            <RefreshCw className="w-4 h-4" /> Bayar Sekarang
          </Button>
          {error && <p className="text-sm text-error mt-3">{error}</p>}
          <p className="text-xs text-text-muted mt-3">
            Status pembayaran diperbarui otomatis setelah Anda menyelesaikan pembayaran.
          </p>
        </div>
      )}

      <div className="flex flex-col gap-3">
        <Link to={`/account/orders/${order.order_number}`}>
          <Button className="w-full" size="lg">
            Lihat Pesanan <ArrowRight className="w-4 h-4" />
          </Button>
        </Link>
        <Link to="/products">
          <Button variant="secondary" className="w-full" size="lg">
            Lanjut Belanja
          </Button>
        </Link>
      </div>
    </div>
  )
}
