import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, Truck, Shield, RefreshCw } from 'lucide-react'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'


interface Category {
  id: number
  name: string
  slug: string
  image_url?: string
  products_count?: number
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

export function HomePage() {
  const [categories, setCategories] = useState<Category[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [newProducts, setNewProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [catRes, featuredRes, newRes] = await Promise.all([
          api.get('/categories'),
          api.get('/products?sort=best_selling&per_page=8'),
          api.get('/products?sort=newest&per_page=8'),
        ])
        setCategories(catRes.data.data || [])
        setFeaturedProducts(featuredRes.data.data || [])
        setNewProducts(newRes.data.data || [])
      } catch (err) {
        console.error('Failed to fetch home data:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchData()
  }, [])

  return (
    <div>
      {/* Hero Section */}
      <section className="relative bg-primary text-primary-inverse overflow-hidden">
        <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-24 relative z-10">
          <div className="max-w-[640px]">
            <h1 className="text-4xl md:text-[64px] font-bold leading-[1.05] tracking-tight mb-6">
              TEMUKAN<br />
              GAYAMU
            </h1>
            <p className="text-lg md:text-xl text-white/70 mb-8 max-w-[480px]">
              Marketplace multi-vendor premium. Produk pilihan dari seller terpercaya.
            </p>
            <Link
              to="/products"
              className="inline-flex items-center gap-2 bg-white text-primary px-8 h-[52px] rounded-full text-base font-medium hover:bg-white/90 active:scale-[0.98] transition-all"
            >
              Belanja Sekarang
              <ArrowRight className="w-5 h-5" />
            </Link>
          </div>
        </div>
        <div className="absolute inset-0 bg-gradient-to-r from-primary via-primary/90 to-primary/70" />
      </section>

      {/* Value Propositions */}
      <section className="border-b border-divider">
        <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 flex items-center justify-between overflow-x-auto gap-8">
          <div className="flex items-center gap-3 flex-shrink-0">
            <Truck className="w-5 h-5 text-text-secondary" />
            <span className="text-sm text-text-secondary whitespace-nowrap">Pengiriman Cepat</span>
          </div>
          <div className="flex items-center gap-3 flex-shrink-0">
            <Shield className="w-5 h-5 text-text-secondary" />
            <span className="text-sm text-text-secondary whitespace-nowrap">Garansi Produk</span>
          </div>
          <div className="flex items-center gap-3 flex-shrink-0">
            <RefreshCw className="w-5 h-5 text-text-secondary" />
            <span className="text-sm text-text-secondary whitespace-nowrap">Mudah Dikembalikan</span>
          </div>
        </div>
      </section>

      {/* Categories Strip */}
      {categories.length > 0 && (
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-10 lg:py-14">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-semibold tracking-tight">Kategori</h2>
            <Link
              to="/categories"
              className="text-sm text-text-secondary hover:text-accent transition-colors flex items-center gap-1"
            >
              Lihat Semua
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
          <div className="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            {categories.slice(0, 6).map((category) => (
              <Link
                key={category.id}
                to={`/categories/${category.slug}`}
                className="group flex flex-col items-center gap-3 p-4 rounded-[16px] bg-surface hover:bg-surface/80 transition-colors"
              >
                <div className="w-12 h-12 rounded-full bg-primary/5 flex items-center justify-center group-hover:bg-primary/10 transition-colors">
                  <span className="text-lg font-bold text-primary">{category.name[0]}</span>
                </div>
                <span className="text-sm font-medium text-text-primary text-center">{category.name}</span>
              </Link>
            ))}
          </div>
        </section>
      )}

      {/* Featured Products */}
      <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-10 lg:py-14">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-semibold tracking-tight">Produk Unggulan</h2>
          <Link
            to="/products?sort=best_selling"
            className="text-sm text-text-secondary hover:text-accent transition-colors flex items-center gap-1"
          >
            Lihat Semua
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
        {isLoading ? (
          <ProductGridSkeleton count={8} />
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
            {featuredProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}
      </section>

      {/* New Arrivals */}
      <section className="bg-surface">
        <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-10 lg:py-14">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-semibold tracking-tight">Produk Terbaru</h2>
            <Link
              to="/products?sort=newest"
              className="text-sm text-text-secondary hover:text-accent transition-colors flex items-center gap-1"
            >
              Lihat Semua
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
          {isLoading ? (
            <ProductGridSkeleton count={8} />
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
              {newProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  )
}
