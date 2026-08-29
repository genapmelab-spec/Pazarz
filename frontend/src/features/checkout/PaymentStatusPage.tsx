import { useEffect, useState } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { CheckCircle, Clock, XCircle, ArrowRight } from 'lucide-react'
import api from '@/lib/api'
import { Button } from '@/components/ui/Button'

export function PaymentStatusPage() {
  const [searchParams] = useSearchParams()
  const orderNumber = searchParams.get('order')
  const [order, setOrder] = useState<any>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    if (!orderNumber) {
      setIsLoading(false)
      return
    }
    const fetchOrder = async () => {
      try {
        const res = await api.get(`/orders/${orderNumber}`)
        setOrder(res.data.data)
      } catch (err) {
        console.error('Failed to fetch order:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchOrder()
  }, [orderNumber])

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

  const status = order.status || 'pending_payment'

  return (
    <div className="max-w-[480px] mx-auto px-5 py-16 text-center">
      {status === 'paid' || status === 'processing' || status === 'completed' ? (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-success/10 flex items-center justify-center">
            <CheckCircle className="w-10 h-10 text-success" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Pembayaran Berhasil!</h1>
          <p className="text-text-secondary mb-1">Pesanan Anda sedang diproses.</p>
          <p className="text-sm text-text-muted mb-8">Nomor pesanan: {order.order_number}</p>
        </>
      ) : status === 'pending_payment' ? (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-warning/10 flex items-center justify-center">
            <Clock className="w-10 h-10 text-warning" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Menunggu Pembayaran</h1>
          <p className="text-text-secondary mb-1">Selesaikan pembayaran dalam waktu yang ditentukan.</p>
          <p className="text-sm text-text-muted mb-8">Nomor pesanan: {order.order_number}</p>
        </>
      ) : (
        <>
          <div className="w-20 h-20 mx-auto mb-6 rounded-full bg-error/10 flex items-center justify-center">
            <XCircle className="w-10 h-10 text-error" />
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Pembayaran Gagal</h1>
          <p className="text-text-secondary mb-8">Terjadi kesalahan saat memproses pembayaran.</p>
        </>
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
