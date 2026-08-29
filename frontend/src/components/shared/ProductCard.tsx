import { Link } from 'react-router-dom'
import { Heart } from 'lucide-react'
import { formatPrice } from '@/lib/utils'
import { cn } from '@/lib/utils'

interface ProductCardProps {
  product: {
    id: number
    name: string
    slug: string
    base_price: number | string
    images?: Array<{ url: string; is_primary: boolean }>
    primary_image?: { url: string } | null
    store?: { name: string; slug: string }
    rating_avg?: number | string
    rating_count?: number
    reviews_avg_rating?: number
    reviews_count?: number
    min_price?: number
    max_price?: number
    is_wishlisted?: boolean
    variants?: Array<{ price: number | string | null }>
  }
  onWishlistToggle?: (productId: number) => void
  className?: string
}

export function ProductCard({ product, onWishlistToggle, className }: ProductCardProps) {
  // Handle both API formats: primary_image (list) and images array (detail)
  const primaryImage = product.primary_image?.url ||
    product.images?.find((img) => img.is_primary)?.url ||
    product.images?.[0]?.url

  // Calculate display price from variants if needed
  const basePrice = Number(product.base_price)
  let displayPrice = basePrice

  // If variants have prices, compute min/max
  if (product.variants && product.variants.length > 0) {
    const prices = product.variants.map(v => Number(v.price)).filter(p => p > 0)
    if (prices.length > 0) {
      const min = Math.min(...prices)
      const max = Math.max(...prices)
      if (min !== max) {
        return (
          <div className={cn('group rounded-[16px] overflow-hidden bg-white', className)}>
            <Link to={`/products/${product.slug}`} className="block">
              <div className="relative aspect-[3/4] bg-surface overflow-hidden">
                {primaryImage ? (
                  <img src={primaryImage} alt={product.name} className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" loading="lazy" />
                ) : (
                  <div className="w-full h-full flex items-center justify-center text-text-muted">
                    <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  </div>
                )}
              </div>
            </Link>
            <div className="p-3">
              <Link to={`/products/${product.slug}`}>
                <h4 className="text-sm font-medium text-text-primary line-clamp-2 mb-1 hover:text-accent transition-colors">{product.name}</h4>
              </Link>
              <p className="text-base font-bold text-text-primary mb-1">{formatPrice(min)} - {formatPrice(max)}</p>
              <div className="flex items-center gap-2">
                {product.rating_avg != null && Number(product.rating_avg) > 0 && (
                  <div className="flex items-center gap-1">
                    <svg className="w-3.5 h-3.5 text-warning fill-warning" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    <span className="text-xs text-text-secondary">{Number(product.rating_avg).toFixed(1)}</span>
                    <span className="text-xs text-text-muted">({product.rating_count || 0})</span>
                  </div>
                )}
                {product.store && <span className="text-xs text-text-muted ml-auto truncate">{product.store.name}</span>}
              </div>
            </div>
            {onWishlistToggle && (
              <button onClick={(e) => { e.preventDefault(); onWishlistToggle(product.id) }} className="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center hover:bg-white transition-colors shadow-elevation-1" aria-label={product.is_wishlisted ? 'Remove from wishlist' : 'Add to wishlist'}>
                <Heart className={cn('w-4 h-4 transition-colors', product.is_wishlisted ? 'fill-error text-error' : 'text-text-secondary')} />
              </button>
            )}
          </div>
        )
      }
    }
  }

  return (
    <div className={cn('group rounded-[16px] overflow-hidden bg-white', className)}>
      <Link to={`/products/${product.slug}`} className="block">
        <div className="relative aspect-[3/4] bg-surface overflow-hidden">
          {primaryImage ? (
            <img src={primaryImage} alt={product.name} className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" loading="lazy" />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-text-muted">
              <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
          )}
          {onWishlistToggle && (
            <button onClick={(e) => { e.preventDefault(); onWishlistToggle(product.id) }} className="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center hover:bg-white transition-colors shadow-elevation-1" aria-label={product.is_wishlisted ? 'Remove from wishlist' : 'Add to wishlist'}>
              <Heart className={cn('w-4 h-4 transition-colors', product.is_wishlisted ? 'fill-error text-error' : 'text-text-secondary')} />
            </button>
          )}
        </div>
      </Link>
      <div className="p-3">
        <Link to={`/products/${product.slug}`}>
          <h4 className="text-sm font-medium text-text-primary line-clamp-2 mb-1 hover:text-accent transition-colors">{product.name}</h4>
        </Link>
        <p className="text-base font-bold text-text-primary mb-1">{formatPrice(displayPrice)}</p>
        <div className="flex items-center gap-2">
          {product.rating_avg != null && Number(product.rating_avg) > 0 && (
            <div className="flex items-center gap-1">
              <svg className="w-3.5 h-3.5 text-warning fill-warning" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
              <span className="text-xs text-text-secondary">{Number(product.rating_avg).toFixed(1)}</span>
              <span className="text-xs text-text-muted">({product.rating_count || 0})</span>
            </div>
          )}
          {product.store && <span className="text-xs text-text-muted ml-auto truncate">{product.store.name}</span>}
        </div>
      </div>
    </div>
  )
}
