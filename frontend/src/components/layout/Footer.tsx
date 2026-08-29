import { Link } from 'react-router-dom'

export function Footer() {
  return (
    <footer className="bg-primary text-primary-inverse mt-auto">
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-12 lg:py-16">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12">
          {/* Brand */}
          <div className="col-span-2 md:col-span-1">
            <span className="text-xl font-bold tracking-[-0.02em]">PAZARZ</span>
            <p className="mt-3 text-sm text-white/50 max-w-[240px] leading-relaxed">
              Marketplace multi-vendor premium. Temukan produk terbaik dari seller terpercaya.
            </p>
          </div>

          {/* Belanja */}
          <div>
            <h4 className="text-[13px] font-semibold uppercase tracking-[0.06em] mb-4 text-white/40">
              Belanja
            </h4>
            <ul className="space-y-3">
              <li>
                <Link to="/products" className="text-sm text-white/60 hover:text-white transition-colors">
                  Semua Produk
                </Link>
              </li>
              <li>
                <Link to="/categories" className="text-sm text-white/60 hover:text-white transition-colors">
                  Kategori
                </Link>
              </li>
            </ul>
          </div>

          {/* Akun */}
          <div>
            <h4 className="text-[13px] font-semibold uppercase tracking-[0.06em] mb-4 text-white/40">
              Akun
            </h4>
            <ul className="space-y-3">
              <li>
                <Link to="/account/orders" className="text-sm text-white/60 hover:text-white transition-colors">
                  Pesanan Saya
                </Link>
              </li>
              <li>
                <Link to="/account/wishlist" className="text-sm text-white/60 hover:text-white transition-colors">
                  Wishlist
                </Link>
              </li>
              <li>
                <Link to="/account/profile" className="text-sm text-white/60 hover:text-white transition-colors">
                  Pengaturan
                </Link>
              </li>
            </ul>
          </div>

          {/* Bantuan */}
          <div>
            <h4 className="text-[13px] font-semibold uppercase tracking-[0.06em] mb-4 text-white/40">
              Bantuan
            </h4>
            <ul className="space-y-3">
              <li>
                <span className="text-sm text-white/60">Hubungi Kami</span>
              </li>
              <li>
                <span className="text-sm text-white/60">FAQ</span>
              </li>
              <li>
                <span className="text-sm text-white/60">Kebijakan Privasi</span>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom bar */}
        <div className="border-t border-white/10 mt-10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs text-white/30">
            &copy; {new Date().getFullYear()} Pazarz. All rights reserved.
          </p>
          <p className="text-xs text-white/30">
            Marketplace Multi-Vendor Premium
          </p>
        </div>
      </div>
    </footer>
  )
}
