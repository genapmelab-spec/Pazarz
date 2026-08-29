import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, ArrowUpRight } from 'lucide-react'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { formatPrice } from '@/lib/utils'

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
  primary_image?: { url: string } | null
  images?: Array<{ url: string; is_primary: boolean }>
  store?: { name: string; slug: string }
  rating_avg?: number
  rating_count?: number
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

  // Get hero image from first featured product or use a fallback
  const heroImage = featuredProducts[0]?.primary_image?.url ||
    featuredProducts[0]?.images?.find(i => i.is_primary)?.url ||
    featuredProducts[0]?.images?.[0]?.url

  return (
    <div>
      {/* Hero Section — Full-bleed with photo background */}
      <section className="relative min-h-[600px] lg:min-h-[720px] flex items-end overflow-hidden">
        {/* Background image */}
        {heroImage ? (
          <img
            src={heroImage}
            alt=""
            className="absolute inset-0 w-full h-full object-cover"
          />
        ) : (
          <div className="absolute inset-0 bg-primary" />
        )}
        {/* Gradient scrim at bottom for text readability */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent" />

        {/* Content */}
        <div className="relative z-10 w-full max-w-[1280px] mx-auto px-5 lg:px-16 pb-16 lg:pb-24">
          <h1 className="text-[40px] md:text-[64px] font-bold leading-[1.05] tracking-[-0.02em] text-white mb-6 max-w-[640px]">
            TEMUKAN
            <br />
            GAYAMU
          </h1>
          <p className="text-lg md:text-xl text-white/70 mb-8 max-w-[480px] leading-relaxed">
            Marketplace multi-vendor premium. Produk pilihan dari seller terpercaya.
          </p>
          <Link
            to="/products"
            className="inline-flex items-center gap-2 bg-white text-primary px-8 h-[52px] rounded-full text-base font-semibold hover:bg-white/90 active:scale-[0.98] transition-all"
          >
            Belanja Sekarang
            <ArrowRight className="w-5 h-5" />
          </Link>
        </div>
      </section>

      {/* Category Strip — horizontal chips */}
      {categories.length > 0 && (
        <section className="border-b border-divider">
          <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-8 lg:py-10">
            <div className="flex items-center justify-between mb-5">
              <h2 className="text-lg font-semibold tracking-tight">Kategori</h2>
              <Link
                to="/categories"
                className="text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1"
              >
                Lihat Semua
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
            <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide">
              {categories.slice(0, 8).map((category) => (
                <Link
                  key={category.id}
                  to={`/categories/${category.slug}`}
                  className="flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-full border border-border bg-white text-sm font-medium text-text-primary hover:bg-surface hover:border-text-muted transition-all"
                >
                  <span className="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                    {category.name[0]}
                  </span>
                  {category.name}
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Trending Section — overlay cards with text at bottom */}
      {featuredProducts.length > 0 && (
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-12 lg:py-16">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-[24px] md:text-[32px] font-semibold tracking-[-0.01em]">
              Trending Sekarang
            </h2>
            <Link
              to="/products?sort=best_selling"
              className="text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1"
            >
              Lihat Semua
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
          {isLoading ? (
            <ProductGridSkeleton count={4} />
          ) : (
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
              {featuredProducts.slice(0, 4).map((product) => {
                const img = product.primary_image?.url ||
                  product.images?.find(i => i.is_primary)?.url ||
                  product.images?.[0]?.url
                return (
                  <Link
                    key={product.id}
                    to={`/products/${product.slug}`}
                    className="group relative aspect-[3/4] rounded-[16px] overflow-hidden bg-surface"
                  >
                    {img ? (
                      <img
                        src={img}
                        alt={product.name}
                        className="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                        loading="lazy"
                      />
                    ) : (
                      <div className="absolute inset-0 flex items-center justify-center text-text-muted">
                        <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                      </div>
                    )}
                    {/* Gradient scrim at bottom */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
                    {/* Text overlay at bottom */}
                    <div className="absolute bottom-0 left-0 right-0 p-4 lg:p-5">
                      <h4 className="text-white text-sm lg:text-base font-semibold line-clamp-2 mb-1">
                        {product.name}
                      </h4>
                      <p className="text-white/80 text-sm font-medium">
                        {formatPrice(product.base_price)}
                      </p>
                    </div>
                    {/* Arrow badge at top-right */}
                    <div className="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                      <ArrowUpRight className="w-4 h-4 text-primary" />
                    </div>
                  </Link>
                )
              })}
            </div>
          )}
        </section>
      )}

      {/* Value Propositions — editorial text section */}
      <section className="bg-surface">
        <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-20">
          <div className="max-w-[800px]">
            <p className="text-xs font-semibold uppercase tracking-[0.1em] text-text-muted mb-4">
              Kenapa Pazarz
            </p>
            <h2 className="text-[24px] md:text-[32px] font-semibold tracking-[-0.01em] leading-[1.2] mb-5">
              Belanja dengan percaya diri. Setiap produk terkurasi, setiap seller terverifikasi.
            </h2>
            <p className="text-base text-text-secondary leading-relaxed max-w-[560px]">
              Pazarz menghubungkan kamu dengan seller premium pilihan. Kualitas terjamin,
              pengiriman cepat, dan pengalaman belanja yang tenang.
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
            <div>
              <p className="text-sm font-semibold text-text-primary mb-1">Kurasi Produk</p>
              <p className="text-sm text-text-secondary">Setiap produk melewati proses kurasi ketat sebelum ditampilkan.</p>
            </div>
            <div>
              <p className="text-sm font-semibold text-text-primary mb-1">Seller Terpercaya</p>
              <p className="text-sm text-text-secondary">Seller diverifikasi oleh tim kami sebelum dapat berjualan.</p>
            </div>
            <div>
              <p className="text-sm font-semibold text-text-primary mb-1">Pengiriman Aman</p>
              <p className="text-sm text-text-secondary">Packing profesional dan asuransi pengiriman untuk setiap pesanan.</p>
            </div>
          </div>
        </div>
      </section>

      {/* Featured Products — product card grid */}
      {featuredProducts.length > 0 && (
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-12 lg:py-16">
          <div className="flex items-center justify-between mb-8">
            <h2 className="text-[24px] md:text-[32px] font-semibold tracking-[-0.01em]">
              Produk Pilihan
            </h2>
            <Link
              to="/products"
              className="text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1"
            >
              Lihat Semua
              <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
          {isLoading ? (
            <ProductGridSkeleton count={8} />
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
              {featuredProducts.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          )}
        </section>
      )}

      {/* New Arrivals */}
      {newProducts.length > 0 && (
        <section className="bg-surface">
          <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-12 lg:py-16">
            <div className="flex items-center justify-between mb-8">
              <h2 className="text-[24px] md:text-[32px] font-semibold tracking-[-0.01em]">
                Baru Saja Tiba
              </h2>
              <Link
                to="/products?sort=newest"
                className="text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1"
              >
                Lihat Semua
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
            {isLoading ? (
              <ProductGridSkeleton count={8} />
            ) : (
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                {newProducts.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>
            )}
          </div>
        </section>
      )}

      {/* CTA Banner */}
      <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-20">
        <div className="bg-primary rounded-[24px] px-8 lg:px-16 py-12 lg:py-16 text-center">
          <h2 className="text-[28px] md:text-[40px] font-bold text-white tracking-[-0.01em] leading-[1.15] mb-4">
            Punya Produk Premium?
          </h2>
          <p className="text-white/60 text-base md:text-lg mb-8 max-w-[480px] mx-auto">
            Bergabung sebagai seller di Pazarz dan jangkau ribuan pembeli yang menghargai kualitas.
          </p>
          <Link
            to="/register"
            className="inline-flex items-center gap-2 bg-white text-primary px-8 h-[52px] rounded-full text-base font-semibold hover:bg-white/90 active:scale-[0.98] transition-all"
          >
            Mulai Berjualan
            <ArrowRight className="w-5 h-5" />
          </Link>
        </div>
      </section>
    </div>
  )
}
