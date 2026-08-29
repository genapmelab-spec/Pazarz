import { useEffect, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { Heart, Minus, Plus, ShoppingBag, Store } from 'lucide-react'
import api from '@/lib/api'
import { useAuthStore } from '@/store/authStore'
import { useCartStore } from '@/store/cartStore'
import { Button } from '@/components/ui/Button'
import { SimpleBadge } from '@/components/ui/Badge'
import { Rating } from '@/components/shared/Rating'
import { ProductCard } from '@/components/shared/ProductCard'
import { formatPrice, cn } from '@/lib/utils'

interface ProductImage {
  id: number
  url: string
  is_primary: boolean
  alt_text?: string
}

interface ProductVariant {
  id: number
  sku: string
  price: number | string | null
  inventory?: { quantity: number; reserved_quantity: number; low_stock_threshold: number }
  stock_quantity?: number
  attribute_values: Array<{
    id: number
    value: string
    attribute: { id: number; name: string } | null
    product_attribute_id?: number
  }>
}

interface Product {
  id: number
  name: string
  slug: string
  description: string
  base_price: number | string
  weight_grams?: number
  status: string
  rating_avg?: number | string
  rating_count?: number
  store: {
    id: number
    name: string
    slug: string
    logo_url?: string
    banner_url?: string
    description?: string
    rating_avg?: number | string
    rating_count?: number
    products_count?: number
  }
  category: { id: number; name: string; slug: string }
  images: ProductImage[]
  variants: ProductVariant[]
  reviews?: Array<any>
  reviews_summary?: {
    average_rating: number
    total_reviews: number
    rating_distribution: Record<number, number>
  }
}

export function ProductDetailPage() {
  const { slug } = useParams<{ slug: string }>()
  const navigate = useNavigate()
  const { isAuthenticated } = useAuthStore()
  const { addItem } = useCartStore()

  const [product, setProduct] = useState<Product | null>(null)
  const [selectedVariant, setSelectedVariant] = useState<ProductVariant | null>(null)
  const [selectedImage, setSelectedImage] = useState<ProductImage | null>(null)
  const [quantity, setQuantity] = useState(1)
  const [activeTab, setActiveTab] = useState<'description' | 'specs' | 'reviews'>('description')
  const [relatedProducts, setRelatedProducts] = useState<any[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [isAddingToCart, setIsAddingToCart] = useState(false)
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null)

  useEffect(() => {
    const fetchProduct = async () => {
      if (!slug) return
      setIsLoading(true)
      try {
        const res = await api.get(`/products/${slug}`)
        const p = res.data.data
        setProduct(p)
        setSelectedImage(p.images?.find((img: ProductImage) => img.is_primary) || p.images?.[0] || null)
        if (p.variants?.length > 0) {
          setSelectedVariant(p.variants[0])
        }
        if (p.category?.slug) {
          const relatedRes = await api.get(`/products?category=${p.category.slug}&per_page=4`)
          setRelatedProducts((relatedRes.data.data || []).filter((rp: any) => rp.id !== p.id).slice(0, 4))
        }
      } catch (err) {
        console.error('Failed to fetch product:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchProduct()
    window.scrollTo(0, 0)
  }, [slug])

  const handleAddToCart = async () => {
    if (!selectedVariant || !isAuthenticated) {
      if (!isAuthenticated) navigate('/login')
      return
    }
    setIsAddingToCart(true)
    try {
      await addItem(selectedVariant.id, quantity)
      setToast({ message: 'Ditambahkan ke keranjang!', type: 'success' })
      setTimeout(() => setToast(null), 3000)
    } catch (err: any) {
      setToast({ message: err.response?.data?.error?.message || 'Gagal menambahkan ke keranjang', type: 'error' })
      setTimeout(() => setToast(null), 3000)
    } finally {
      setIsAddingToCart(false)
    }
  }

  // Build attribute groups — handle case where attribute may be null
  const attributeGroups = product?.variants?.reduce((groups, variant) => {
    variant.attribute_values.forEach((av) => {
      const attrName = av.attribute?.name || `Option ${av.product_attribute_id || av.id}`
      if (!groups[attrName]) groups[attrName] = new Map<string, ProductVariant>()
      groups[attrName].set(av.value, variant)
    })
    return groups
  }, {} as Record<string, Map<string, ProductVariant>>) || {}

  const currentPrice = selectedVariant?.price
    ? Number(selectedVariant.price)
    : Number(product?.base_price || 0)
  const stockQuantity = selectedVariant?.inventory?.quantity ?? selectedVariant?.stock_quantity ?? 0
  const inStock = stockQuantity > 0

  if (isLoading) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
          <div className="aspect-square bg-surface rounded-[16px] skeleton" />
          <div className="space-y-4">
            <div className="h-8 w-3/4 skeleton" />
            <div className="h-6 w-1/3 skeleton" />
            <div className="h-10 w-1/4 skeleton" />
            <div className="h-12 w-full skeleton" />
          </div>
        </div>
      </div>
    )
  }

  if (!product) {
    return (
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-20 text-center">
        <h1 className="text-2xl font-semibold mb-2">Produk tidak ditemukan</h1>
        <p className="text-text-muted mb-6">Produk yang Anda cari tidak tersedia atau telah dihapus.</p>
        <Button onClick={() => navigate('/products')}>Kembali ke Produk</Button>
      </div>
    )
  }

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      {toast && (
        <div className={cn(
          'fixed top-4 right-4 z-50 px-4 py-3 rounded-[12px] shadow-elevation-2 text-sm',
          toast.type === 'success' ? 'bg-success/10 text-success border border-success/20' : 'bg-error/10 text-error border border-error/20'
        )}>
          {toast.message}
        </div>
      )}

      <nav className="text-sm text-text-muted mb-6">
        <Link to="/" className="hover:text-accent transition-colors">Beranda</Link>
        <span className="mx-2">/</span>
        <Link to={`/categories/${product.category?.slug}`} className="hover:text-accent transition-colors">{product.category?.name}</Link>
        <span className="mx-2">/</span>
        <span className="text-text-primary font-medium">{product.name}</span>
      </nav>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
        {/* Gallery */}
        <div className="space-y-3">
          <div className="aspect-square rounded-[16px] overflow-hidden bg-surface">
            {selectedImage ? (
              <img src={selectedImage.url} alt={selectedImage.alt_text || product.name} className="w-full h-full object-cover" />
            ) : (
              <div className="w-full h-full flex items-center justify-center text-text-muted">
                <svg className="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            )}
          </div>
          {product.images.length > 1 && (
            <div className="flex gap-2 overflow-x-auto pb-2">
              {product.images.map((image) => (
                <button
                  key={image.id}
                  onClick={() => setSelectedImage(image)}
                  className={cn(
                    'w-20 h-20 rounded-[10px] overflow-hidden flex-shrink-0 border-2 transition-colors',
                    selectedImage?.id === image.id ? 'border-primary' : 'border-transparent hover:border-border'
                  )}
                >
                  <img src={image.url} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Info */}
        <div className="space-y-6">
          <div>
            <h1 className="text-2xl lg:text-[32px] font-semibold tracking-tight leading-tight">{product.name}</h1>
            <div className="flex items-center gap-3 mt-3">
              <Rating
                value={Number(product.rating_avg) || 0}
                count={product.rating_count || 0}
              />
            </div>
          </div>

          <div className="flex items-baseline gap-3">
            <span className="text-[32px] font-bold text-primary">{formatPrice(currentPrice)}</span>
          </div>

          {/* Attribute Variant Selectors */}
          {Object.keys(attributeGroups).length > 0 && (
            <div className="space-y-4">
              {Object.entries(attributeGroups).map(([attrName, valueMap]) => (
                <div key={attrName}>
                  <p className="text-sm font-medium text-text-primary mb-2">{attrName}</p>
                  <div className="flex flex-wrap gap-2">
                    {Array.from(valueMap.entries()).map(([value, variant]) => {
                      const isSelected = selectedVariant?.id === variant.id
                      return (
                        <button
                          key={value}
                          onClick={() => { setSelectedVariant(variant); setQuantity(1) }}
                          className={cn(
                            'px-4 py-2 rounded-full border text-sm transition-all',
                            isSelected
                              ? 'border-primary bg-primary text-white'
                              : 'border-border hover:border-text-muted'
                          )}
                        >
                          {value}
                        </button>
                      )
                    })}
                  </div>
                </div>
              ))}
            </div>
          )}

          {/* Stock */}
          {selectedVariant && (
            <div>
              {inStock ? (
                <SimpleBadge variant={stockQuantity <= 5 ? 'warning' : 'success'}>Stok: {stockQuantity}</SimpleBadge>
              ) : (
                <SimpleBadge variant="error">Stok Habis</SimpleBadge>
              )}
            </div>
          )}

          {/* Quantity */}
          <div>
            <p className="text-sm font-medium text-text-primary mb-2">Jumlah</p>
            <div className="inline-flex items-center h-11 border border-border rounded-[10px] overflow-hidden">
              <button onClick={() => setQuantity(Math.max(1, quantity - 1))} className="w-11 h-full flex items-center justify-center hover:bg-surface transition-colors" disabled={quantity <= 1}>
                <Minus className="w-4 h-4" />
              </button>
              <span className="w-12 text-center text-sm font-medium">{quantity}</span>
              <button onClick={() => setQuantity(Math.min(stockQuantity, quantity + 1))} className="w-11 h-full flex items-center justify-center hover:bg-surface transition-colors" disabled={quantity >= stockQuantity}>
                <Plus className="w-4 h-4" />
              </button>
            </div>
          </div>

          {/* CTA */}
          <div className="flex gap-3">
            <Button onClick={handleAddToCart} isLoading={isAddingToCart} disabled={!inStock} className="flex-1" size="lg">
              <ShoppingBag className="w-5 h-5" />
              {!inStock ? 'Stok Habis' : 'Tambah ke Keranjang'}
            </Button>
            <Button variant="secondary" size="lg" className="w-12 flex-shrink-0" aria-label="Add to wishlist">
              <Heart className="w-5 h-5" />
            </Button>
          </div>

          {/* Store */}
          {product.store && (
            <Link to={`/stores/${product.store.slug}`} className="flex items-center gap-3 p-4 rounded-[16px] border border-divider hover:bg-surface transition-colors">
              <div className="w-10 h-10 rounded-[12px] bg-primary/5 flex items-center justify-center overflow-hidden flex-shrink-0">
                {product.store.logo_url ? (
                  <img src={product.store.logo_url} alt="" className="w-full h-full object-cover" />
                ) : (
                  <Store className="w-5 h-5 text-text-muted" />
                )}
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-text-primary truncate">{product.store.name}</p>
                <p className="text-xs text-text-muted">{product.store.products_count || 0} produk</p>
              </div>
              <span className="text-xs text-accent font-medium">Kunjungi Toko</span>
            </Link>
          )}
        </div>
      </div>

      {/* Tabs */}
      <div className="mt-12 border-b border-divider">
        <div className="flex gap-8">
          {(['description', 'specs', 'reviews'] as const).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={cn(
                'pb-3 text-sm font-medium border-b-2 transition-colors -mb-[1px]',
                activeTab === tab ? 'border-primary text-text-primary' : 'border-transparent text-text-muted hover:text-text-secondary'
              )}
            >
              {tab === 'description' ? 'Deskripsi' : tab === 'specs' ? 'Spesifikasi' : `Review (${product.reviews?.length || product.reviews_summary?.total_reviews || 0})`}
            </button>
          ))}
        </div>
      </div>

      <div className="py-8">
        {activeTab === 'description' && (
          <p className="text-text-secondary leading-relaxed whitespace-pre-wrap">{product.description || 'Tidak ada deskripsi.'}</p>
        )}
        {activeTab === 'specs' && (
          <div className="grid grid-cols-2 gap-x-8 gap-y-3">
            {selectedVariant?.sku && (<><span className="text-sm text-text-muted">SKU</span><span className="text-sm text-text-primary">{selectedVariant.sku}</span></>)}
            {product.weight_grams && (<><span className="text-sm text-text-muted">Berat</span><span className="text-sm text-text-primary">{product.weight_grams}g</span></>)}
            {selectedVariant?.attribute_values.map((av) => (
              <div key={av.id} className="contents">
                <span className="text-sm text-text-muted">{av.attribute?.name || 'Attribut'}</span>
                <span className="text-sm text-text-primary">{av.value}</span>
              </div>
            ))}
          </div>
        )}
        {activeTab === 'reviews' && (
          <div className="text-center py-8 text-text-muted text-sm">
            {(!product.reviews || product.reviews.length === 0) && !product.reviews_summary
              ? 'Belum ada review untuk produk ini.'
              : `Menampilkan ${product.reviews?.length || product.reviews_summary?.total_reviews || 0} review.`}
          </div>
        )}
      </div>

      {relatedProducts.length > 0 && (
        <section className="mt-12 pt-12 border-t border-divider">
          <h2 className="text-2xl font-semibold tracking-tight mb-6">Produk Terkait</h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
            {relatedProducts.map((p) => (<ProductCard key={p.id} product={p} />))}
          </div>
        </section>
      )}
    </div>
  )
}
