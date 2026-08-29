import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { Heart, MapPin, User, ShoppingBag } from 'lucide-react'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { cn } from '@/lib/utils'

interface WishlistProduct {
  id: number
  name: string
  slug: string
  base_price: number
  images?: Array<{ url: string; is_primary: boolean }>
  store?: { name: string; slug: string }
  reviews_avg_rating?: number
  reviews_count?: number
}

const NAV_ITEMS = [
  { path: '/account/profile', label: 'Profil', icon: User },
  { path: '/account/addresses', label: 'Alamat', icon: MapPin },
  { path: '/account/wishlist', label: 'Wishlist', icon: Heart },
  { path: '/account/orders', label: 'Pesanan', icon: ShoppingBag },
]

export function WishlistPage() {
  const location = useLocation()
  const [products, setProducts] = useState<WishlistProduct[]>([])
  const [isLoading, setIsLoading] = useState(true)

  const fetchWishlist = async () => {
    try {
      const res = await api.get('/wishlist')
      setProducts(res.data.data || [])
    } catch (err) {
      console.error('Failed to fetch wishlist:', err)
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => { fetchWishlist() }, [])

  const handleToggleWishlist = async (productId: number) => {
    try {
      await api.post('/wishlist', { product_id: productId })
      setProducts((prev) => prev.filter((p) => p.id !== productId))
    } catch (err) {
      console.error('Failed to toggle wishlist:', err)
    }
  }

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <div className="flex flex-col lg:flex-row gap-8">
        {/* Sidebar */}
        <aside className="w-full lg:w-[260px] flex-shrink-0">
          <nav className="flex lg:flex-col gap-2 overflow-x-auto pb-2 lg:pb-0">
            {NAV_ITEMS.map((item) => {
              const Icon = item.icon
              const isActive = location.pathname === item.path
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  className={cn(
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition-colors',
                    isActive ? 'bg-surface text-text-primary' : 'text-text-secondary hover:bg-surface/50 hover:text-text-primary'
                  )}
                >
                  <Icon className="w-4 h-4" />
                  {item.label}
                </Link>
              )
            })}
          </nav>
        </aside>

        {/* Content */}
        <div className="flex-1">
          <h1 className="text-2xl font-bold tracking-tight mb-6">Wishlist Saya</h1>

          {isLoading ? (
            <ProductGridSkeleton count={8} />
          ) : products.length === 0 ? (
            <div className="text-center py-16">
              <Heart className="w-16 h-16 text-text-muted mx-auto mb-4" />
              <h3 className="text-lg font-medium mb-2">Wishlist Kosong</h3>
              <p className="text-sm text-text-muted">Simpan produk favorit Anda di sini.</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
              {products.map((product) => (
                <ProductCard
                  key={product.id}
                  product={{ ...product, is_wishlisted: true }}
                  onWishlistToggle={handleToggleWishlist}
                />
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
