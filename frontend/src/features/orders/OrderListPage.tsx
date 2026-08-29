import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Package } from 'lucide-react'
import api from '@/lib/api'
import { Badge } from '@/components/ui/Badge'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { formatPrice, formatDate } from '@/lib/utils'
import { cn } from '@/lib/utils'

interface Order {
  id: number
  order_number: string
  status: string
  grand_total: number
  created_at: string
  sub_orders: Array<{
    id: number
    status: string
    store: { name: string }
    items: Array<{              product_name_snapshot: string
      quantity: number
      price_snapshot: number
    }>
  }>
}

const STATUS_TABS = [
  { value: '', label: 'Semua' },
  { value: 'pending_payment', label: 'Belum Bayar' },
  { value: 'paid', label: 'Dibayar' },
  { value: 'processing', label: 'Diproses' },
  { value: 'shipped', label: 'Dikirim' },
  { value: 'completed', label: 'Selesai' },
  { value: 'cancelled', label: 'Dibatalkan' },
]

export function OrderListPage() {
  const [orders, setOrders] = useState<Order[]>([])
  const [activeTab, setActiveTab] = useState('')
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchOrders = async () => {
      setIsLoading(true)
      try {
        const params: Record<string, string> = {}
        if (activeTab) params.status = activeTab
        const res = await api.get('/orders', { params })
        setOrders(res.data.data || [])
      } catch (err) {
        console.error('Failed to fetch orders:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchOrders()
  }, [activeTab])

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <h1 className="text-[32px] font-bold tracking-tight mb-6">Pesanan Saya</h1>

      {/* Status Tabs */}
      <div className="flex gap-1 overflow-x-auto pb-4 mb-6 border-b border-divider">
        {STATUS_TABS.map((tab) => (
          <button
            key={tab.value}
            onClick={() => setActiveTab(tab.value)}
            className={cn(
              'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
              activeTab === tab.value
                ? 'bg-primary text-white'
                : 'text-text-secondary hover:bg-surface'
            )}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Orders */}
      {isLoading ? (
        <div className="space-y-4">
          {[1, 2, 3].map((i) => (
            <div key={i} className="p-6 rounded-[16px] border border-divider">
              <div className="flex justify-between mb-3">
                <div className="h-5 w-40 skeleton" />
                <div className="h-5 w-20 skeleton" />
              </div>
              <div className="h-4 w-64 skeleton" />
            </div>
          ))}
        </div>
      ) : orders.length === 0 ? (
        <div className="text-center py-16">
          <Package className="w-16 h-16 text-text-muted mx-auto mb-4" />
          <h3 className="text-lg font-medium text-text-primary mb-2">
            {activeTab ? 'Tidak ada pesanan dengan status ini' : 'Belum ada pesanan'}
          </h3>
          <p className="text-sm text-text-muted">
            Mulai belanja untuk melihat pesanan Anda di sini.
          </p>
        </div>
      ) : (
        <div className="space-y-4">
          {orders.map((order) => (
            <Link
              key={order.id}
              to={`/account/orders/${order.order_number}`}
              className="block p-5 rounded-[16px] border border-divider hover:border-text-muted hover:shadow-elevation-1 transition-all"
            >
              <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-3">
                  <span className="text-sm font-medium text-text-primary">#{order.order_number}</span>
                  <Badge status={order.status} size="sm" />
                </div>
                <span className="text-xs text-text-muted">{formatDate(order.created_at)}</span>
              </div>

              {order.sub_orders?.map((subOrder) => (
                <div key={subOrder.id} className="flex items-center gap-3 py-2 border-t border-divider first:border-0 first:pt-0 last:pb-0">
                  <div className="flex-1 min-w-0">
                    <p className="text-sm text-text-secondary truncate">
                      {subOrder.store?.name} · {subOrder.items?.length || 0} item
                    </p>
                  </div>
                  <Badge status={subOrder.status} size="sm" />
                </div>
              ))}

              <div className="flex justify-end mt-3 pt-3 border-t border-divider">
                <span className="text-base font-bold text-text-primary">{formatPrice(order.grand_total)}</span>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}
