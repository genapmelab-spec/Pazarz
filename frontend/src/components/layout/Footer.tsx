import { Link } from 'react-router-dom'
import { ArrowUpRight } from 'lucide-react'
import { Reveal, Stagger, StaggerItem } from '@/components/ui/Reveal'

export function Footer() {
  return (
    <footer className="relative bg-primary text-primary-inverse mt-auto overflow-hidden">
      {/* Decorative elements */}
      <div className="absolute top-0 right-0 w-96 h-96 bg-white/[0.02] rounded-full -translate-y-1/2 translate-x-1/2" />
      <div className="absolute bottom-0 left-0 w-72 h-72 bg-white/[0.02] rounded-full translate-y-1/2 -translate-x-1/2" />

      <div className="relative max-w-[1280px] mx-auto px-5 lg:px-16 py-16 lg:py-20">
        <Stagger className="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12" staggerDelay={0.08}>
          {/* Brand */}
          <StaggerItem className="col-span-2 md:col-span-1">
            <span className="text-2xl font-bold tracking-[-0.03em]">PAZARZ</span>
            <p className="mt-4 text-sm text-white/40 max-w-[260px] leading-relaxed">
              Marketplace multi-vendor premium. Temukan produk terbaik dari seller terpercaya.
            </p>
            <div className="flex items-center gap-1 mt-5">
              <div className="w-8 h-[1px] bg-white/20" />
              <span className="text-[10px] font-semibold uppercase tracking-[0.15em] text-white/30">
                Est. 2026
              </span>
            </div>
          </StaggerItem>

          {/* Belanja */}
          <StaggerItem>
            <h4 className="text-[11px] font-semibold uppercase tracking-[0.15em] mb-5 text-white/30">
              Belanja
            </h4>
            <ul className="space-y-3.5">
              <li>
                <Link to="/products" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Semua Produk
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
              <li>
                <Link to="/categories" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Kategori
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
              <li>
                <Link to="/products?sort=newest" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Produk Baru
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
            </ul>
          </StaggerItem>

          {/* Akun */}
          <StaggerItem>
            <h4 className="text-[11px] font-semibold uppercase tracking-[0.15em] mb-5 text-white/30">
              Akun
            </h4>
            <ul className="space-y-3.5">
              <li>
                <Link to="/account/orders" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Pesanan Saya
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
              <li>
                <Link to="/account/wishlist" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Wishlist
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
              <li>
                <Link to="/account/profile" className="group inline-flex items-center gap-1 text-sm text-white/50 hover:text-white transition-colors duration-200">
                  Pengaturan
                  <ArrowUpRight className="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                </Link>
              </li>
            </ul>
          </StaggerItem>

          {/* Bantuan */}
          <StaggerItem>
            <h4 className="text-[11px] font-semibold uppercase tracking-[0.15em] mb-5 text-white/30">
              Bantuan
            </h4>
            <ul className="space-y-3.5">
              <li>
                <span className="text-sm text-white/50 cursor-default">Hubungi Kami</span>
              </li>
              <li>
                <span className="text-sm text-white/50 cursor-default">FAQ</span>
              </li>
              <li>
                <span className="text-sm text-white/50 cursor-default">Kebijakan Privasi</span>
              </li>
            </ul>
          </StaggerItem>
        </Stagger>

        {/* Bottom bar */}
        <div className="border-t border-white/10 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs text-white/25">
            &copy; {new Date().getFullYear()} Pazarz. All rights reserved.
          </p>
          <div className="flex items-center gap-6">
            <p className="text-xs text-white/25">
              Marketplace Multi-Vendor Premium
            </p>
          </div>
        </div>
      </div>
    </footer>
  )
}
