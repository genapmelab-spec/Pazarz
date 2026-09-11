import { useEffect, useState, Suspense } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight, ArrowUpRight, Sparkles, Shield, Truck, Star } from 'lucide-react'
import { motion, useScroll, useTransform } from 'framer-motion'
import api from '@/lib/api'
import { ProductCard } from '@/components/shared/ProductCard'
import { ProductGridSkeleton } from '@/components/ui/Skeleton'
import { formatPrice } from '@/lib/utils'
import { Reveal, Stagger, StaggerItem } from '@/components/ui/Reveal'
import { TiltCard } from '@/components/ui/TiltCard'
import { HeroBackground } from '@/components/three/HeroBackground'

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

const categoryIcons: Record<string, string> = {
  Fashion: '👔',
  Elektronik: '⚡',
  Rumah: '🏠',
  Kecantikan: '💄',
  Olahraga: '🏃',
  Makanan: '🍜',
  Otomotif: '🚗',
  Seni: '🎨',
}

export function HomePage() {
  const [categories, setCategories] = useState<Category[]>([])
  const [featuredProducts, setFeaturedProducts] = useState<Product[]>([])
  const [newProducts, setNewProducts] = useState<Product[]>([])
  const [isLoading, setIsLoading] = useState(true)

  const { scrollYProgress } = useScroll()
  const heroOpacity = useTransform(scrollYProgress, [0, 0.15], [1, 0.6])
  const heroScale = useTransform(scrollYProgress, [0, 0.15], [1, 0.98])

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
    <div className="overflow-hidden">
      {/* ═══════════════════════════════════════════════════════════
          HERO SECTION — Full-bleed with 3D Three.js background
         ═══════════════════════════════════════════════════════════ */}
      <motion.section
        className="relative w-full h-[480px] sm:h-[560px] lg:h-[680px] overflow-hidden bg-primary"
        style={{ opacity: heroOpacity, scale: heroScale }}
      >
        {/* 3D Background — Three.js particles */}
        <Suspense fallback={null}>
          <HeroBackground />
        </Suspense>

        {/* Hero image with parallax */}
        <motion.img
          src="/images/hero-314.png"
          alt="Pazarz Hero"
          className="absolute inset-0 w-full h-full object-cover object-center z-0 mix-blend-luminosity opacity-60"
        />

        {/* Gradient overlays — editorial style */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent z-[1]" />
        <div className="absolute inset-0 bg-gradient-to-r from-black/40 to-transparent z-[1]" />

        {/* Content — editorial layout */}
        <div className="absolute bottom-0 left-0 right-0 w-full max-w-[1280px] mx-auto px-5 lg:px-16 pb-12 lg:pb-20 z-[2]">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.3, ease: [0.25, 0.4, 0.25, 1] }}
          >
            <div className="flex items-center gap-2 mb-4">
              <div className="w-8 h-[2px] bg-white/40" />
              <span className="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/50">
                Premium Marketplace
              </span>
            </div>

            <h1 className="text-[40px] sm:text-[56px] lg:text-[72px] font-bold leading-[0.95] tracking-[-0.03em] text-white mb-5 max-w-[600px]">
              TEMUKAN
              <br />
              <span className="text-white/60">GAYAMU</span>
            </h1>

            <p className="text-sm sm:text-base md:text-lg text-white/50 mb-8 max-w-[420px] leading-relaxed font-light">
              Marketplace multi-vendor premium. Produk pilihan dari seller terpercaya.
            </p>

            <div className="flex items-center gap-4">
              <Link
                to="/products"
                className="group inline-flex items-center gap-2.5 bg-white text-primary px-8 h-[52px] rounded-full text-sm font-semibold hover:bg-white/90 active:scale-[0.97] transition-all duration-200"
              >
                Belanja Sekarang
                <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
              </Link>
              <Link
                to="/categories"
                className="inline-flex items-center gap-2 text-white/60 hover:text-white text-sm font-medium transition-colors"
              >
                Lihat Kategori
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          </motion.div>
        </div>

        {/* Decorative element — bottom right */}
        <div className="absolute bottom-8 right-8 lg:right-16 z-[2] hidden lg:block">
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 1, delay: 0.6 }}
            className="w-24 h-24 border border-white/10 rounded-full flex items-center justify-center"
          >
            <motion.div
              animate={{ rotate: 360 }}
              transition={{ duration: 20, repeat: Infinity, ease: 'linear' }}
              className="w-16 h-16 border border-white/10 rounded-full flex items-center justify-center"
            >
              <Sparkles className="w-4 h-4 text-white/20" />
            </motion.div>
          </motion.div>
        </div>
      </motion.section>

      {/* ═══════════════════════════════════════════════════════════
          CATEGORY STRIP — Animated horizontal chips
         ═══════════════════════════════════════════════════════════ */}
      {categories.length > 0 && (
        <section className="border-b border-divider">
          <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-8 lg:py-10">
            <Reveal>
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
            </Reveal>
            <Stagger className="flex gap-3 overflow-x-auto pb-2 scrollbar-hide" staggerDelay={0.05}>
              {categories.slice(0, 8).map((category) => (
                <StaggerItem key={category.id}>
                  <Link
                    to={`/categories/${category.slug}`}
                    className="group flex-shrink-0 inline-flex items-center gap-2.5 px-5 py-3 rounded-full border border-border bg-white text-sm font-medium text-text-primary hover:bg-surface hover:border-text-muted hover:shadow-elevation-1 transition-all duration-200"
                  >
                    <span className="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm flex-shrink-0 group-hover:scale-110 transition-transform duration-200">
                      {categoryIcons[category.name] || category.name[0]}
                    </span>
                    {category.name}
                  </Link>
                </StaggerItem>
              ))}
            </Stagger>
          </div>
        </section>
      )}

      {/* ═══════════════════════════════════════════════════════════
          TRENDING — Overlay cards with premium hover effects
         ═══════════════════════════════════════════════════════════ */}
      {featuredProducts.length > 0 && (
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-14 lg:py-20">
          <Reveal>
            <div className="flex items-center justify-between mb-10">
              <div>
                <span className="text-[11px] font-semibold uppercase tracking-[0.15em] text-text-muted block mb-2">
                  Trending
                </span>
                <h2 className="text-[28px] md:text-[36px] font-semibold tracking-[-0.02em] leading-tight">
                  Trending Sekarang
                </h2>
              </div>
              <Link
                to="/products?sort=best_selling"
                className="group text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1.5"
              >
                Lihat Semua
                <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
              </Link>
            </div>
          </Reveal>

          {isLoading ? (
            <ProductGridSkeleton count={4} />
          ) : (
            <Stagger className="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5" staggerDelay={0.1}>
              {featuredProducts.slice(0, 4).map((product) => {
                const img =
                  product.primary_image?.url ||
                  product.images?.find((i) => i.is_primary)?.url ||
                  product.images?.[0]?.url
                return (
                  <StaggerItem key={product.id}>
                    <TiltCard>
                      <Link
                        to={`/products/${product.slug}`}
                        className="group relative aspect-[3/4] rounded-[16px] overflow-hidden bg-surface block"
                      >
                        {img ? (
                          <img
                            src={img}
                            alt={product.name}
                            className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            loading="lazy"
                          />
                        ) : (
                          <div className="absolute inset-0 flex items-center justify-center text-text-muted">
                            <svg className="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
                        )}
                        {/* Premium gradient overlay */}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent" />
                        {/* Text overlay — editorial style */}
                        <div className="absolute bottom-0 left-0 right-0 p-5 lg:p-6">
                          <h4 className="text-white text-sm lg:text-base font-semibold line-clamp-2 mb-2 drop-shadow-lg">
                            {product.name}
                          </h4>
                          <p className="text-white/70 text-sm font-medium">
                            {formatPrice(product.base_price)}
                          </p>
                        </div>
                        {/* Arrow badge — appears on hover */}
                        <div className="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center opacity-0 group-hover:opacity-100 scale-75 group-hover:scale-100 transition-all duration-300">
                          <ArrowUpRight className="w-4 h-4 text-white" />
                        </div>
                        {/* Corner accent line */}
                        <div className="absolute top-0 left-0 w-0 h-[3px] bg-white/50 group-hover:w-full transition-all duration-500" />
                      </Link>
                    </TiltCard>
                  </StaggerItem>
                )
              })}
            </Stagger>
          )}
        </section>
      )}

      {/* ═══════════════════════════════════════════════════════════
          VALUE PROPOSITIONS — Editorial section with icons
         ═══════════════════════════════════════════════════════════ */}
      <section className="bg-surface">
        <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-24">
          <Reveal>
            <div className="max-w-[800px] mb-12">
              <div className="flex items-center gap-2 mb-4">
                <div className="w-8 h-[2px] bg-primary/20" />
                <span className="text-[11px] font-semibold uppercase tracking-[0.2em] text-text-muted">
                  Kenapa Pazarz
                </span>
              </div>
              <h2 className="text-[24px] md:text-[36px] font-semibold tracking-[-0.02em] leading-[1.15] mb-5">
                Belanja dengan percaya diri.
                <br />
                <span className="text-text-secondary">Setiap produk terkurasi, setiap seller terverifikasi.</span>
              </h2>
              <p className="text-base text-text-secondary leading-relaxed max-w-[560px]">
                Pazarz menghubungkan kamu dengan seller premium pilihan. Kualitas terjamin,
                pengiriman cepat, dan pengalaman belanja yang tenang.
              </p>
            </div>
          </Reveal>

          <Stagger className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8" staggerDelay={0.12}>
            {[
              { icon: Sparkles, title: 'Kurasi Produk', desc: 'Setiap produk melewati proses kurasi ketat sebelum ditampilkan.', accent: 'bg-accent/10 text-accent' },
              { icon: Shield, title: 'Seller Terpercaya', desc: 'Seller diverifikasi oleh tim kami sebelum dapat berjualan.', accent: 'bg-success/10 text-success' },
              { icon: Truck, title: 'Pengiriman Aman', desc: 'Packing profesional dan asuransi pengiriman untuk setiap pesanan.', accent: 'bg-warning/10 text-warning' },
            ].map(({ icon: Icon, title, desc, accent }) => (
              <StaggerItem key={title}>
                <div className="group p-6 lg:p-8 rounded-[20px] bg-white border border-divider hover:shadow-elevation-2 hover:border-transparent transition-all duration-300">
                  <div className={`w-12 h-12 rounded-[14px] ${accent} flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300`}>
                    <Icon className="w-5 h-5" />
                  </div>
                  <h3 className="text-base font-semibold mb-2">{title}</h3>
                  <p className="text-sm text-text-secondary leading-relaxed">{desc}</p>
                </div>
              </StaggerItem>
            ))}
          </Stagger>
        </div>
      </section>

      {/* ═══════════════════════════════════════════════════════════
          FEATURED PRODUCTS — Premium grid
         ═══════════════════════════════════════════════════════════ */}
      {featuredProducts.length > 0 && (
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-14 lg:py-20">
          <Reveal>
            <div className="flex items-center justify-between mb-10">
              <div>
                <span className="text-[11px] font-semibold uppercase tracking-[0.15em] text-text-muted block mb-2">
                  Pilihan Kami
                </span>
                <h2 className="text-[28px] md:text-[36px] font-semibold tracking-[-0.02em] leading-tight">
                  Produk Pilihan
                </h2>
              </div>
              <Link
                to="/products"
                className="group text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1.5"
              >
                Lihat Semua
                <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
              </Link>
            </div>
          </Reveal>

          {isLoading ? (
            <ProductGridSkeleton count={8} />
          ) : (
            <Stagger className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5" staggerDelay={0.06}>
              {featuredProducts.map((product) => (
                <StaggerItem key={product.id}>
                  <ProductCard product={product} />
                </StaggerItem>
              ))}
            </Stagger>
          )}
        </section>
      )}

      {/* ═══════════════════════════════════════════════════════════
          NEW ARRIVALS — Light surface bg
         ═══════════════════════════════════════════════════════════ */}
      {newProducts.length > 0 && (
        <section className="bg-surface">
          <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-14 lg:py-20">
            <Reveal>
              <div className="flex items-center justify-between mb-10">
                <div>
                  <span className="text-[11px] font-semibold uppercase tracking-[0.15em] text-text-muted block mb-2">
                    Fresh Drop
                  </span>
                  <h2 className="text-[28px] md:text-[36px] font-semibold tracking-[-0.02em] leading-tight">
                    Baru Saja Tiba
                  </h2>
                </div>
                <Link
                  to="/products?sort=newest"
                  className="group text-sm text-text-secondary hover:text-primary transition-colors flex items-center gap-1.5"
                >
                  Lihat Semua
                  <ArrowRight className="w-4 h-4 group-hover:translate-x-0.5 transition-transform" />
                </Link>
              </div>
            </Reveal>

            {isLoading ? (
              <ProductGridSkeleton count={8} />
            ) : (
              <Stagger className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5" staggerDelay={0.06}>
                {newProducts.map((product) => (
                  <StaggerItem key={product.id}>
                    <ProductCard product={product} />
                  </StaggerItem>
                ))}
              </Stagger>
            )}
          </div>
        </section>
      )}

      {/* ═══════════════════════════════════════════════════════════
          CTA BANNER — Premium seller recruitment
         ═══════════════════════════════════════════════════════════ */}
      <Reveal>
        <section className="max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-20">
          <div className="relative bg-primary rounded-[28px] px-8 lg:px-16 py-14 lg:py-18 text-center overflow-hidden">
            {/* Decorative circles */}
            <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2" />
            <div className="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2" />

            <div className="relative z-10">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Star className="w-4 h-4 text-white/40" />
                <span className="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/40">
                  Join as Seller
                </span>
                <Star className="w-4 h-4 text-white/40" />
              </div>

              <h2 className="text-[28px] md:text-[44px] font-bold text-white tracking-[-0.02em] leading-[1.1] mb-4">
                Punya Produk Premium?
              </h2>
              <p className="text-white/50 text-base md:text-lg mb-10 max-w-[500px] mx-auto leading-relaxed">
                Bergabung sebagai seller di Pazarz dan jangkau ribuan pembeli yang menghargai kualitas.
              </p>
              <Link
                to="/register"
                className="group inline-flex items-center gap-2.5 bg-white text-primary px-10 h-[56px] rounded-full text-base font-semibold hover:bg-white/90 active:scale-[0.97] transition-all duration-200 shadow-[0_8px_32px_rgba(255,255,255,0.15)]"
              >
                Mulai Berjualan
                <ArrowRight className="w-5 h-5 transition-transform group-hover:translate-x-1" />
              </Link>
            </div>
          </div>
        </section>
      </Reveal>
    </div>
  )
}
