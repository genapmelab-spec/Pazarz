import { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { ArrowLeft, Package, Truck, CheckCircle, Clock } from 'lucide-react'
import api from '@/lib/api'
import { Button } from '@/components/ui/Button'
import { Badge } from '@/components/ui/Badge'
import { formatPrice, formatDate, cn } from '@/lib/utils'

interface OrderDetail {
  id: number
  order_number: string
  status: string
  grand_total: number
  shipping_total: number
  discount_total: number
  created_at: string
  shipping_address?: {
    recipient_name: string
    phone: string
    address_line: string
    city: string
    province: string
    postal_code: string
  }
  payment?: {
    method: string
    status: string
    amount: number
  }
  sub_orders: Array<{
    id: number
    order_number: string
    status: string
    shipping_cost: number
    store: { id: number; name: string; slug: string }
    items: Array<{
      id: number
      product_name_snapshot: string
      variant_label_snapshot?: string
      quantity: number
      price_snapshot: number
      sku: string
    }>
    shipment?: {
      tracking_number: string
      courier: string
      status: string
      tracking_events?: Array<{
        status: string
        description: string
        created_at: string
      }>
    }
  }>
}

const TIMELINE_STEPS = ['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'completed']

export function OrderDetailPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [order, setOrder] = useState<OrderDetail | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchOrder = async () => {
      if (!id) return
      setIsLoading(true)
      try {
        const res = await api.get(`/orders/${id}`)
        setOrder(res.data.data)
      } catch (err) {
        console.error('Failed to fetch order:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchOrder()
  }, [id])

  const handleCompleteOrder = async () => {
    if (!order) return
    try {
      await api.post(`/orders/${order.order_number}/complete`)
      setOrder({ ...order, status: 'completed' })
    } catch (err) {
      console.error('Failed to complete order:', err)
    }
  }

  const getTimelineIndex = (status: string) => TIMELINE_STEPS.indexOf(status)

  if (isLoading) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6">
        <div className="h-8 w-48 skeleton mb-6" />
        <div className="h-64 skeleton rounded-[16px]" />
      </div>
    )
  }

  if (!order) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-20 text-center">
        <h1 className="text-2xl font-semibold">Pesanan tidak ditemukan</h1>
      </div>
    )
  }

  const currentIdx = getTimelineIndex(order.status)

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      {/* Header */}
      <div className="flex items-center gap-4 mb-6">
        <button onClick={() => navigate(-1)} className="p-2 rounded-full hover:bg-surface transition-colors">
          <ArrowLeft className="w-5 h-5" />
        </button>
        <div>
          <h1 className="text-2xl font-bold tracking-tight">Pesanan #{order.order_number}</h1>
          <p className="text-sm text-text-muted">{formatDate(order.created_at)}</p>
        </div>
        <div className="ml-auto">
          <Badge status={order.status} />
        </div>
      </div>

      {/* Timeline */}
      <div className="mb-8 overflow-x-auto pb-2">
        <div className="flex items-center min-w-[600px]">
          {TIMELINE_STEPS.map((step, i) => {
            const isCompleted = i <= currentIdx
            const isCurrent = i === currentIdx
            return (
              <div key={step} className="flex items-center flex-1">
                <div className="flex flex-col items-center">
                  <div className={cn(
                    'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-colors',
                    isCompleted ? 'bg-primary text-white' : 'bg-surface text-text-muted',
                    isCurrent && 'ring-4 ring-primary/20'
                  )}>
                    {i + 1}
                  </div>
                  <span className={cn(
                    'text-xs mt-2 whitespace-nowrap',
                    isCompleted ? 'text-text-primary font-medium' : 'text-text-muted'
                  )}>
                    {step === 'pending_payment' ? 'Bayar' :
                     step === 'paid' ? 'Dibayar' :
                     step === 'processing' ? 'Diproses' :
                     step === 'shipped' ? 'Dikirim' :
                     step === 'delivered' ? 'Diterima' : 'Selesai'}
                  </span>
                </div>
                {i < TIMELINE_STEPS.length - 1 && (
                  <div className={cn(
                    'flex-1 h-0.5 mx-2',
                    i < currentIdx ? 'bg-primary' : 'bg-border'
                  )} />
                )}
              </div>
            )
          })}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          {/* Sub-Orders */}
          {order.sub_orders?.map((subOrder) => (
            <div key={subOrder.id} className="rounded-[16px] border border-divider p-6">
              <div className="flex items-center justify-between mb-4">
                <Link
                  to={`/stores/${subOrder.store?.slug}`}
                  className="text-sm font-semibold hover:text-accent transition-colors"
                >
                  {subOrder.store?.name}
                </Link>
                <Badge status={subOrder.status} size="sm" />
              </div>

              <div className="space-y-3">
                {subOrder.items?.map((item) => (
                  <div key={item.id} className="flex items-center gap-3">
                    <div className="w-12 h-12 rounded-[8px] bg-surface flex items-center justify-center flex-shrink-0">
                      <Package className="w-5 h-5 text-text-muted" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium line-clamp-1">{item.product_name_snapshot}</p>
                      {item.variant_label_snapshot && (
                        <p className="text-xs text-text-muted">{item.variant_label_snapshot}</p>
                      )}
                      <p className="text-xs text-text-muted">× {item.quantity}</p>
                    </div>
                    <span className="text-sm font-medium">{formatPrice(item.price_snapshot * item.quantity)}</span>
                  </div>
                ))}
              </div>

              {/* Shipment Info */}
              {subOrder.shipment && (
                <div className="mt-4 pt-4 border-t border-divider">
                  <div className="flex items-center gap-2 text-sm text-text-secondary">
                    <Truck className="w-4 h-4" />
                    <span>{subOrder.shipment.courier} · {subOrder.shipment.tracking_number}</span>
                    <Badge status={subOrder.shipment.status} size="sm" />
                  </div>
                  {subOrder.shipment.tracking_events?.map((event, i) => (
                    <div key={i} className="flex gap-3 mt-3 text-sm">
                      <Clock className="w-4 h-4 text-text-muted flex-shrink-0 mt-0.5" />
                      <div>
                        <p className="text-text-secondary">{event.description}</p>
                        <p className="text-xs text-text-muted">{formatDate(event.created_at)}</p>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              {/* Sub-Order Actions */}
              <div className="mt-4 pt-4 border-t border-divider flex gap-2">
                {subOrder.status === 'delivered' && (
                  <Button size="sm" onClick={handleCompleteOrder}>
                    <CheckCircle className="w-4 h-4" /> Konfirmasi Diterima
                  </Button>
                )}
              </div>
            </div>
          ))}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Payment Summary */}
          <div className="rounded-[16px] border border-divider p-6">
            <h3 className="text-sm font-semibold mb-4">Ringkasan Pembayaran</h3>
            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-text-secondary">Subtotal</span>
                <span>{formatPrice(order.grand_total - order.shipping_total + (order.discount_total || 0))}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-text-secondary">Ongkir</span>
                <span>{formatPrice(order.shipping_total)}</span>
              </div>
              {(order.discount_total || 0) > 0 && (
                <div className="flex justify-between text-success">
                  <span>Diskon</span>
                  <span>-{formatPrice(order.discount_total)}</span>
                </div>
              )}
              <div className="flex justify-between font-bold pt-2 border-t border-divider">
                <span>Total</span>
                <span>{formatPrice(order.grand_total)}</span>
              </div>
            </div>
          </div>

          {/* Shipping Address */}
          {order.shipping_address && (
            <div className="rounded-[16px] border border-divider p-6">
              <h3 className="text-sm font-semibold mb-3">Alamat Pengiriman</h3>
              <div className="text-sm text-text-secondary space-y-1">
                <p className="font-medium text-text-primary">{order.shipping_address.recipient_name}</p>
                <p>{order.shipping_address.phone}</p>
                <p>{order.shipping_address.address_line}</p>
                <p>{order.shipping_address.city}, {order.shipping_address.province} {order.shipping_address.postal_code}</p>
              </div>
            </div>
          )}

          {/* Payment Method */}
          {order.payment && (
            <div className="rounded-[16px] border border-divider p-6">
              <h3 className="text-sm font-semibold mb-3">Metode Pembayaran</h3>
              <p className="text-sm text-text-secondary">{order.payment.method || 'Transfer Bank'}</p>
              <Badge status={order.payment.status} size="sm" className="mt-2" />
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
