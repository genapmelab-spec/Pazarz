import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { Store, Package, Star } from 'lucide-react'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { Button } from '@/components/ui/Button'

interface StoreDetail {
  id: number
  name: string
  slug: string
  description?: string
  logo_url?: string
  banner_url?: string
  rating?: number
  products_count?: number
  followers_count?: number
}

interface Product {
  id: number
  name: string
  slug: string
  base_price: number
  images?: Array<{ url: string; is_primary: boolean }>
  store?: { name: string; slug: string }
  reviews_avg_rating?: number
  reviews_count?: number
}

export function StoreDetailPage() {
  const { slug } = useParams<{ slug: string }>()
  const [store, setStore] = useState<StoreDetail | null>(null)
  const [products, setProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [activeTab, setActiveTab] = useState<'products' | 'about'>('products')

  useEffect(() => {
    const fetchStore = async () => {
      if (!slug) return
      setIsLoading(true)
      try {
        const [storeRes, productsRes] = await Promise.all([
          api.get(`/stores/${slug}`),
          api.get(`/stores/${slug}/products`),
        ])
        setStore(storeRes.data.data)
        setProducts(productsRes.data.data || [])
      } catch (err) {
        console.error('Failed to fetch store:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchStore()
  }, [slug])

  if (isLoading) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6">
        <div className="h-48 bg-surface rounded-[16px] skeleton mb-6" />
        <ProductGridSkeleton count={8} />
      </div>
    )
  }

  if (!store) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-20 text-center">
        <h1 className="text-2xl font-semibold">Toko tidak ditemukan</h1>
      </div>
    )
  }

  return (
    <div>
      {/* Banner */}
      <div className="h-48 bg-primary/5 relative overflow-hidden">
        {store.banner_url && (
          <img src={store.banner_url} alt="" className="w-full h-full object-cover" />
        )}
      </div>

      {/* Store Info */}
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16">
        <div className="flex items-end gap-4 -mt-8 mb-6 relative z-10">
          <div className="w-20 h-20 rounded-[16px] bg-white border-4 border-white shadow-elevation-2 flex items-center justify-center overflow-hidden flex-shrink-0">
            {store.logo_url ? (
              <img src={store.logo_url} alt="" className="w-full h-full object-cover" />
            ) : (
              <Store className="w-8 h-8 text-text-muted" />
            )}
          </div>
          <div className="flex-1 min-w-0 pb-1">
            <h1 className="text-2xl font-semibold tracking-tight">{store.name}</h1>
            <div className="flex items-center gap-4 mt-1 text-sm text-text-muted">
              {store.rating != null && (
                <span className="flex items-center gap-1">
                  <Star className="w-4 h-4 text-warning fill-warning" />
                  {store.rating.toFixed(1)}
                </span>
              )}
              <span className="flex items-center gap-1">
                <Package className="w-4 h-4" />
                {store.products_count || 0} produk
              </span>
              {store.followers_count != null && (
                <span>{store.followers_count} followers</span>
              )}
            </div>
          </div>
          <Button variant="secondary" size="sm">Follow Toko</Button>
        </div>

        {/* Tabs */}
        <div className="border-b border-divider mb-6">
          <div className="flex gap-6">
            <button
              onClick={() => setActiveTab('products')}
              className={`pb-3 text-sm font-medium border-b-2 transition-colors -mb-[1px] ${
                activeTab === 'products' ? 'border-primary text-text-primary' : 'border-transparent text-text-muted hover:text-text-secondary'
              }`}
            >
              Produk
            </button>
            <button
              onClick={() => setActiveTab('about')}
              className={`pb-3 text-sm font-medium border-b-2 transition-colors -mb-[1px] ${
                activeTab === 'about' ? 'border-primary text-text-primary' : 'border-transparent text-text-muted hover:text-text-secondary'
              }`}
            >
              Tentang
            </button>
          </div>
        </div>

        {/* Content */}
        {activeTab === 'products' && (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6 pb-12">
            {products.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}

        {activeTab === 'about' && (
          <div className="pb-12 max-w-[640px]">
            <p className="text-text-secondary leading-relaxed whitespace-pre-wrap">
              {store.description || 'Tidak ada deskripsi toko.'}
            </p>
          </div>
        )}
      </div>
    </div>
  )
}
