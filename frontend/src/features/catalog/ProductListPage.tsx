import { useEffect, useState, useCallback } from 'react'
import { useSearchParams, Link } from 'react-router-dom'
import { SlidersHorizontal, X, ChevronDown } from 'lucide-react'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { Button } from '@/components/ui/Button'
import { cn } from '@/lib/utils'

interface Product {
  id: number
  name: string
  slug: string
  base_price: number
  min_price?: number
  max_price?: number
  images?: Array<{ url: string; is_primary: boolean }>
  store?: { name: string; slug: string }
  reviews_avg_rating?: number
  reviews_count?: number
  is_wishlisted?: boolean
}

interface Category {
  id: number
  name: string
  slug: string
  children?: Category[]
}

interface PaginationMeta {
  current_page: number
  per_page: number
  total: number
  last_page: number
}

const SORT_OPTIONS = [
  { value: 'newest', label: 'Terbaru' },
  { value: 'price_asc', label: 'Harga Terendah' },
  { value: 'price_desc', label: 'Harga Tertinggi' },
  { value: 'best_selling', label: 'Terlaris' },
  { value: 'rating', label: 'Rating Tertinggi' },
]

const PRICE_RANGES = [
  { min: 0, max: 100000, label: 'Di bawah Rp 100.000' },
  { min: 100000, max: 500000, label: 'Rp 100.000 - 500.000' },
  { min: 500000, max: 1000000, label: 'Rp 500.000 - 1.000.000' },
  { min: 1000000, max: null, label: 'Di atas Rp 1.000.000' },
]

export function ProductListPage() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [products, setProducts] = useState<Product[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [pagination, setPagination] = useState<PaginationMeta | null>(null)
  const [isLoading, setIsLoading] = useState(true)
  const [showFilters, setShowFilters] = useState(false)

  const currentPage = parseInt(searchParams.get('page') || '1')
  const currentSort = searchParams.get('sort') || 'newest'
  const currentCategory = searchParams.get('category') || ''
  const currentMinPrice = searchParams.get('min_price') || ''
  const currentMaxPrice = searchParams.get('max_price') || ''
  const currentRating = searchParams.get('rating_min') || ''
  const searchQuery = searchParams.get('q') || ''

  const updateParams = useCallback((key: string, value: string) => {
    setSearchParams((prev) => {
      const next = new URLSearchParams(prev)
      if (value) {
        next.set(key, value)
      } else {
        next.delete(key)
      }
      if (key !== 'page') next.delete('page')
      return next
    })
  }, [setSearchParams])

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const res = await api.get('/categories')
        setCategories(res.data.data || [])
      } catch (err) {
        console.error('Failed to fetch categories:', err)
      }
    }
    fetchCategories()
  }, [])

  useEffect(() => {
    const fetchProducts = async () => {
      setIsLoading(true)
      try {
        const params: Record<string, string> = {
          page: currentPage.toString(),
          per_page: '24',
          sort: currentSort,
        }
        if (searchQuery) params.q = searchQuery
        if (currentCategory) params.category = currentCategory
        if (currentMinPrice) params.min_price = currentMinPrice
        if (currentMaxPrice) params.max_price = currentMaxPrice
        if (currentRating) params.rating_min = currentRating

        const res = await api.get('/products', { params })
        setProducts(res.data.data || [])
        setPagination(res.data.meta || null)
      } catch (err) {
        console.error('Failed to fetch products:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchProducts()
  }, [currentPage, currentSort, currentCategory, currentMinPrice, currentMaxPrice, currentRating, searchQuery])

  const activeFilters = [
    currentCategory && 'Kategori',
    currentMinPrice || currentMaxPrice ? 'Harga' : '',
    currentRating ? 'Rating' : '',
  ].filter(Boolean)

  const clearFilters = () => {
    setSearchParams((prev) => {
      const next = new URLSearchParams()
      if (searchQuery) next.set('q', searchQuery)
      return next
    })
  }

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      {/* Breadcrumb */}
      <nav className="text-sm text-text-muted mb-4">
        <Link to="/" className="hover:text-accent transition-colors">Beranda</Link>
        <span className="mx-2">/</span>
        <span className="text-text-primary font-medium">
          {searchQuery ? `Hasil pencarian "${searchQuery}"` : 'Semua Produk'}
        </span>
      </nav>

      <div className="flex gap-8">
        {/* Sidebar Filters - Desktop */}
        <aside className="hidden lg:block w-[260px] flex-shrink-0">
          <div className="sticky top-24 space-y-6">
            <div>
              <h3 className="text-sm font-semibold text-text-primary mb-3">Kategori</h3>
              <div className="space-y-1">
                <button
                  onClick={() => updateParams('category', '')}
                  className={cn(
                    'block w-full text-left text-sm py-1.5 px-2 rounded-md transition-colors',
                    !currentCategory ? 'text-accent font-medium bg-accent/5' : 'text-text-secondary hover:text-text-primary hover:bg-surface'
                  )}
                >
                  Semua Kategori
                </button>
                {categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => updateParams('category', cat.slug)}
                    className={cn(
                      'block w-full text-left text-sm py-1.5 px-2 rounded-md transition-colors',
                      currentCategory === cat.slug ? 'text-accent font-medium bg-accent/5' : 'text-text-secondary hover:text-text-primary hover:bg-surface'
                    )}
                  >
                    {cat.name}
                  </button>
                ))}
              </div>
            </div>

            <div>
              <h3 className="text-sm font-semibold text-text-primary mb-3">Harga</h3>
              <div className="space-y-1">
                {PRICE_RANGES.map((range, i) => (
                  <button
                    key={i}
                    onClick={() => {
                      updateParams('min_price', range.min.toString())
                      updateParams('max_price', range.max?.toString() || '')
                    }}
                    className={cn(
                      'block w-full text-left text-sm py-1.5 px-2 rounded-md transition-colors',
                      'text-text-secondary hover:text-text-primary hover:bg-surface'
                    )}
                  >
                    {range.label}
                  </button>
                ))}
              </div>
            </div>

            <div>
              <h3 className="text-sm font-semibold text-text-primary mb-3">Rating</h3>
              <div className="space-y-1">
                {[4, 3, 2, 1].map((rating) => (
                  <button
                    key={rating}
                    onClick={() => updateParams('rating_min', rating.toString())}
                    className={cn(
                      'block w-full text-left text-sm py-1.5 px-2 rounded-md transition-colors',
                      'text-text-secondary hover:text-text-primary hover:bg-surface'
                    )}
                  >
                    {rating}★ ke atas
                  </button>
                ))}
              </div>
            </div>
          </div>
        </aside>

        {/* Main Content */}
        <div className="flex-1 min-w-0">
          {/* Top Bar */}
          <div className="flex items-center justify-between mb-4 gap-4">
            <div className="flex items-center gap-3">
              <button
                onClick={() => setShowFilters(!showFilters)}
                className="lg:hidden flex items-center gap-2 h-9 px-3 rounded-full border border-border text-sm hover:bg-surface transition-colors"
              >
                <SlidersHorizontal className="w-4 h-4" />
                Filter
              </button>

              {activeFilters.length > 0 && (
                <div className="flex items-center gap-2">
                  {activeFilters.map((f) => (
                    <span key={f} className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-accent/10 text-accent text-xs">
                      {f}
                      <X className="w-3 h-3 cursor-pointer" />
                    </span>
                  ))}
                  <button onClick={clearFilters} className="text-xs text-text-muted hover:text-error transition-colors">
                    Hapus Semua
                  </button>
                </div>
              )}
            </div>

            <div className="flex items-center gap-2">
              <span className="text-sm text-text-muted hidden sm:inline">
                {pagination?.total || 0} produk
              </span>
              <div className="relative">
                <select
                  value={currentSort}
                  onChange={(e) => updateParams('sort', e.target.value)}
                  className="h-9 pl-3 pr-8 rounded-full border border-border text-sm bg-white appearance-none focus:outline-none focus:border-accent"
                >
                  {SORT_OPTIONS.map((opt) => (
                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                  ))}
                </select>
                <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
              </div>
            </div>
          </div>

          {/* Mobile Filters Drawer */}
          {showFilters && (
            <div className="lg:hidden fixed inset-0 z-50 bg-black/40" onClick={() => setShowFilters(false)}>
              <div
                className="absolute bottom-0 left-0 right-0 bg-white rounded-t-[20px] p-6 max-h-[80vh] overflow-y-auto"
                onClick={(e) => e.stopPropagation()}
              >
                <div className="flex items-center justify-between mb-6">
                  <h2 className="text-lg font-semibold">Filter</h2>
                  <button onClick={() => setShowFilters(false)}>
                    <X className="w-5 h-5" />
                  </button>
                </div>
                <div className="space-y-6">
                  <div>
                    <h3 className="text-sm font-semibold mb-3">Kategori</h3>
                    <div className="space-y-2">
                      {categories.map((cat) => (
                        <button
                          key={cat.id}
                          onClick={() => { updateParams('category', cat.slug); setShowFilters(false) }}
                          className={cn(
                            'block w-full text-left text-sm py-2 px-3 rounded-lg transition-colors',
                            currentCategory === cat.slug ? 'bg-accent/10 text-accent font-medium' : 'hover:bg-surface'
                          )}
                        >
                          {cat.name}
                        </button>
                      ))}
                    </div>
                  </div>
                </div>
                <Button className="w-full mt-6" onClick={() => setShowFilters(false)}>
                  Terapkan Filter
                </Button>
              </div>
            </div>
          )}

          {/* Products Grid */}
          {isLoading ? (
            <ProductGridSkeleton count={12} />
          ) : products.length === 0 ? (
            <div className="text-center py-20">
              <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-surface flex items-center justify-center">
                <svg className="w-8 h-8 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <h3 className="text-lg font-medium text-text-primary mb-2">Produk tidak ditemukan</h3>
              <p className="text-sm text-text-muted mb-4">Coba ubah filter atau kata kunci pencarian</p>
              <Button variant="secondary" onClick={clearFilters}>Reset Filter</Button>
            </div>
          ) : (
            <>
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                {products.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>

              {/* Pagination */}
              {pagination && pagination.last_page > 1 && (
                <div className="flex items-center justify-center gap-2 mt-10">
                  {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                    <button
                      key={page}
                      onClick={() => updateParams('page', page.toString())}
                      className={cn(
                        'w-8 h-8 rounded-full text-sm font-medium transition-colors',
                        page === currentPage
                          ? 'bg-primary text-white'
                          : 'text-text-secondary hover:bg-surface'
                      )}
                    >
                      {page}
                    </button>
                  ))}
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  )
}
