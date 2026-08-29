import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Minus, Plus, Trash2, ShoppingBag } from 'lucide-react'
import { useCartStore } from '@/store/cartStore'
import { useAuthStore } from '@/store/authStore'
import { Button } from '@/components/ui/Button'
import { formatPrice, cn } from '@/lib/utils'

export function CartPage() {
  const { items, fetchCart, updateItem, removeItem, isLoading } = useCartStore()
  const { isAuthenticated } = useAuthStore()
  const navigate = useNavigate()
  const [selectedItems, setSelectedItems] = useState<Set<number>>(new Set())
  const [updatingId, setUpdatingId] = useState<number | null>(null)

  useEffect(() => {
    if (isAuthenticated) {
      fetchCart()
    }
  }, [isAuthenticated, fetchCart])

  // Select all items by default
  useEffect(() => {
    if (items.length > 0 && selectedItems.size === 0) {
      setSelectedItems(new Set(items.map((item) => item.id)))
    }
  }, [items])

  // Group items by store
  const groupedItems = items.reduce((groups, item) => {
    const storeName = item.variant.product.store.name
    if (!groups[storeName]) groups[storeName] = []
    groups[storeName].push(item)
    return groups
  }, {} as Record<string, typeof items>)

  const selectedCartItems = items.filter((item) => selectedItems.has(item.id))
  const subtotal = selectedCartItems.reduce((sum, item) => sum + item.price_snapshot * item.quantity, 0)

  const toggleItem = (itemId: number) => {
    setSelectedItems((prev) => {
      const next = new Set(prev)
      if (next.has(itemId)) {
        next.delete(itemId)
      } else {
        next.add(itemId)
      }
      return next
    })
  }

  const toggleAll = () => {
    if (selectedItems.size === items.length) {
      setSelectedItems(new Set())
    } else {
      setSelectedItems(new Set(items.map((item) => item.id)))
    }
  }

  const handleUpdateQuantity = async (itemId: number, newQuantity: number) => {
    if (newQuantity < 1) return
    setUpdatingId(itemId)
    try {
      await updateItem(itemId, newQuantity)
    } catch {
      // Error handled by store
    } finally {
      setUpdatingId(null)
    }
  }

  const handleRemove = async (itemId: number) => {
    await removeItem(itemId)
    setSelectedItems((prev) => {
      const next = new Set(prev)
      next.delete(itemId)
      return next
    })
  }

  if (!isAuthenticated) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-20 text-center">
        <ShoppingBag className="w-16 h-16 text-text-muted mx-auto mb-4" />
        <h1 className="text-2xl font-semibold mb-2">Keranjang Kosong</h1>
        <p className="text-text-muted mb-6">Masuk untuk melihat keranjang Anda</p>
        <Button onClick={() => navigate('/login')}>Masuk</Button>
      </div>
    )
  }

  if (items.length === 0) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-20 text-center">
        <ShoppingBag className="w-16 h-16 text-text-muted mx-auto mb-4" />
        <h1 className="text-2xl font-semibold mb-2">Keranjang Kosong</h1>
        <p className="text-text-muted mb-6">Mulai belanja dan temukan produk favorit Anda</p>
        <Button onClick={() => navigate('/products')}>Mulai Belanja</Button>
      </div>
    )
  }

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <h1 className="text-[32px] font-bold tracking-tight mb-8">Keranjang</h1>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Cart Items */}
        <div className="flex-1">
          {/* Select All */}
          <div className="flex items-center gap-3 pb-4 border-b border-divider mb-4">
            <button
              onClick={toggleAll}
              className={cn(
                'w-5 h-5 rounded-md border-2 flex items-center justify-center transition-colors',
                selectedItems.size === items.length
                  ? 'bg-primary border-primary'
                  : 'border-border hover:border-text-muted'
              )}
            >
              {selectedItems.size === items.length && (
                <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                </svg>
              )}
            </button>
            <span className="text-sm text-text-secondary">
              Pilih Semua ({items.length} item)
            </span>
          </div>

          {/* Grouped by Store */}
          {Object.entries(groupedItems).map(([storeName, storeItems]) => (
            <div key={storeName} className="mb-6">
              <div className="flex items-center gap-2 mb-3">
                <Link
                  to={`/stores/${storeItems[0].variant.product.store.slug}`}
                  className="text-sm font-medium text-text-primary hover:text-accent transition-colors"
                >
                  {storeName}
                </Link>
              </div>

              <div className="space-y-3">
                {storeItems.map((item) => {
                  const imageUrl = item.variant.product_images?.find((img) => img.is_primary)?.url ||
                    item.variant.product_images?.[0]?.url
                  return (
                    <div key={item.id} className="flex items-center gap-4 p-4 rounded-[16px] border border-divider bg-white">
                      <button
                        onClick={() => toggleItem(item.id)}
                        className={cn(
                          'w-5 h-5 rounded-md border-2 flex items-center justify-center transition-colors flex-shrink-0',
                          selectedItems.has(item.id)
                            ? 'bg-primary border-primary'
                            : 'border-border hover:border-text-muted'
                        )}
                      >
                        {selectedItems.has(item.id) && (
                          <svg className="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                          </svg>
                        )}
                      </button>

                      <div className="w-16 h-16 rounded-[8px] bg-surface overflow-hidden flex-shrink-0">
                        {imageUrl ? (
                          <img src={imageUrl} alt="" className="w-full h-full object-cover" />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center text-text-muted">
                            <ShoppingBag className="w-5 h-5" />
                          </div>
                        )}
                      </div>

                      <div className="flex-1 min-w-0">
                        <Link
                          to={`/products/${item.variant.product.slug}`}
                          className="text-sm font-medium text-text-primary hover:text-accent line-clamp-1 block"
                        >
                          {item.variant.product.name}
                        </Link>
                        <p className="text-xs text-text-muted mt-0.5">
                          {item.variant.attribute_values?.map((av) => av.value).join(' / ')}
                        </p>
                      </div>

                      <div className="text-right flex-shrink-0">
                        <p className="text-sm font-bold text-text-primary">{formatPrice(item.price_snapshot)}</p>
                        <div className="flex items-center gap-2 mt-1">
                          <button
                            onClick={() => handleUpdateQuantity(item.id, item.quantity - 1)}
                            disabled={item.quantity <= 1 || updatingId === item.id}
                            className="w-7 h-7 rounded-full border border-border flex items-center justify-center hover:bg-surface transition-colors disabled:opacity-40"
                          >
                            <Minus className="w-3 h-3" />
                          </button>
                          <span className="text-sm font-medium w-6 text-center">{item.quantity}</span>
                          <button
                            onClick={() => handleUpdateQuantity(item.id, item.quantity + 1)}
                            disabled={updatingId === item.id}
                            className="w-7 h-7 rounded-full border border-border flex items-center justify-center hover:bg-surface transition-colors disabled:opacity-40"
                          >
                            <Plus className="w-3 h-3" />
                          </button>
                        </div>
                      </div>

                      <button
                        onClick={() => handleRemove(item.id)}
                        className="p-2 rounded-full hover:bg-error/5 text-text-muted hover:text-error transition-colors flex-shrink-0"
                        aria-label="Remove item"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  )
                })}
              </div>
            </div>
          ))}
        </div>

        {/* Summary - Desktop */}
        <div className="w-full lg:w-[360px] flex-shrink-0">
          <div className="sticky top-24 bg-white rounded-[16px] border border-divider p-6">
            <h3 className="text-lg font-semibold mb-4">Ringkasan</h3>

            <div className="space-y-3 mb-4">
              <div className="flex justify-between text-sm">
                <span className="text-text-secondary">Subtotal ({selectedCartItems.length} item)</span>
                <span className="font-medium">{formatPrice(subtotal)}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-text-secondary">Estimasi Ongkir</span>
                <span className="text-text-muted">Dihitung saat checkout</span>
              </div>
            </div>

            <div className="border-t border-divider pt-4 mb-6">
              <div className="flex justify-between">
                <span className="font-semibold">Total</span>
                <span className="text-xl font-bold">{formatPrice(subtotal)}</span>
              </div>
            </div>

            <Button
              onClick={() => navigate('/checkout')}
              className="w-full"
              size="lg"
              disabled={selectedCartItems.length === 0}
            >
              Checkout ({selectedCartItems.length})
            </Button>
          </div>
        </div>
      </div>
    </div>
  )
}
