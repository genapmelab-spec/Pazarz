import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { Search, ShoppingBag, User, Menu, X, LogOut } from 'lucide-react'
import { useAuthStore } from '@/store/authStore'
import { useCartStore } from '@/store/cartStore'
import { cn } from '@/lib/utils'

export function Header() {
  const [searchQuery, setSearchQuery] = useState('')
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const [profileMenuOpen, setProfileMenuOpen] = useState(false)
  const { user, isAuthenticated, logout } = useAuthStore()
  const { itemCount } = useCartStore()
  const navigate = useNavigate()
  const location = useLocation()

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`)
      setSearchQuery('')
    }
  }

  const handleLogout = async () => {
    await logout()
    setProfileMenuOpen(false)
    navigate('/')
  }

  const isActive = (path: string) => location.pathname === path

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-divider">
      <div className="max-w-[1280px] mx-auto px-5 lg:px-16">
        <div className="flex items-center justify-between h-[72px]">
          {/* Logo */}
          <Link to="/" className="flex-shrink-0">
            <span className="text-2xl font-bold tracking-tight text-primary">PAZARZ</span>
          </Link>

          {/* Search - Desktop */}
          <form onSubmit={handleSearch} className="hidden md:flex flex-1 max-w-[480px] mx-8">
            <div className="relative w-full">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Cari produk..."
                className="w-full h-10 pl-11 pr-4 rounded-full bg-surface border border-transparent text-sm placeholder:text-text-muted focus:outline-none focus:border-accent focus:bg-white transition-colors"
              />
            </div>
          </form>

          {/* Navigation - Desktop */}
          <nav className="hidden md:flex items-center gap-6">
            <Link
              to="/products"
              className={cn(
                'text-sm font-medium transition-colors hover:text-primary',
                isActive('/products') ? 'text-primary' : 'text-text-secondary'
              )}
            >
              Produk
            </Link>
            <Link
              to="/categories"
              className={cn(
                'text-sm font-medium transition-colors hover:text-primary',
                isActive('/categories') ? 'text-primary' : 'text-text-secondary'
              )}
            >
              Kategori
            </Link>
          </nav>

          {/* Right Actions */}
          <div className="flex items-center gap-2">
            {/* Cart */}
            <Link
              to="/cart"
              className="relative p-2.5 rounded-full hover:bg-surface transition-colors"
              aria-label="Shopping cart"
            >
              <ShoppingBag className="w-5 h-5 text-text-primary" />
              {itemCount > 0 && (
                <span className="absolute -top-0.5 -right-0.5 w-5 h-5 bg-primary text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                  {itemCount > 99 ? '99+' : itemCount}
                </span>
              )}
            </Link>

            {/* Profile / Auth */}
            {isAuthenticated ? (
              <div className="relative">
                <button
                  onClick={() => setProfileMenuOpen(!profileMenuOpen)}
                  className="p-2.5 rounded-full hover:bg-surface transition-colors"
                  aria-label="Account menu"
                >
                  <User className="w-5 h-5 text-text-primary" />
                </button>

                {profileMenuOpen && (
                  <>
                    <div className="fixed inset-0 z-40" onClick={() => setProfileMenuOpen(false)} />
                    <div className="absolute right-0 top-full mt-2 w-56 bg-white rounded-[16px] shadow-elevation-3 border border-divider py-2 z-50">
                      <div className="px-4 py-2 border-b border-divider">
                        <p className="text-sm font-medium text-text-primary truncate">{user?.name}</p>
                        <p className="text-xs text-text-muted truncate">{user?.email}</p>
                      </div>
                      <Link
                        to="/account/orders"
                        onClick={() => setProfileMenuOpen(false)}
                        className="block px-4 py-2.5 text-sm text-text-primary hover:bg-surface transition-colors"
                      >
                        Pesanan Saya
                      </Link>
                      <Link
                        to="/account/wishlist"
                        onClick={() => setProfileMenuOpen(false)}
                        className="block px-4 py-2.5 text-sm text-text-primary hover:bg-surface transition-colors"
                      >
                        Wishlist
                      </Link>
                      <Link
                        to="/account/addresses"
                        onClick={() => setProfileMenuOpen(false)}
                        className="block px-4 py-2.5 text-sm text-text-primary hover:bg-surface transition-colors"
                      >
                        Alamat
                      </Link>
                      <Link
                        to="/account/profile"
                        onClick={() => setProfileMenuOpen(false)}
                        className="block px-4 py-2.5 text-sm text-text-primary hover:bg-surface transition-colors"
                      >
                        Pengaturan
                      </Link>
                      <div className="border-t border-divider mt-1 pt-1">
                        <button
                          onClick={handleLogout}
                          className="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-error hover:bg-error/5 transition-colors"
                        >
                          <LogOut className="w-4 h-4" />
                          Keluar
                        </button>
                      </div>
                    </div>
                  </>
                )}
              </div>
            ) : (
              <Link
                to="/login"
                className="text-sm font-medium text-text-primary hover:text-accent transition-colors px-3 py-2"
              >
                Masuk
              </Link>
            )}

            {/* Mobile Menu Toggle */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden p-2.5 rounded-full hover:bg-surface transition-colors"
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? (
                <X className="w-5 h-5 text-text-primary" />
              ) : (
                <Menu className="w-5 h-5 text-text-primary" />
              )}
            </button>
          </div>
        </div>

        {/* Mobile Search */}
        <form onSubmit={handleSearch} className="md:hidden pb-3">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Cari produk..."
              className="w-full h-10 pl-10 pr-4 rounded-full bg-surface border border-transparent text-sm placeholder:text-text-muted focus:outline-none focus:border-accent focus:bg-white transition-colors"
            />
          </div>
        </form>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden border-t border-divider bg-white">
          <nav className="max-w-[1280px] mx-auto px-5 py-4 flex flex-col gap-2">
            <Link
              to="/products"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2 text-sm font-medium text-text-primary hover:text-accent"
            >
              Produk
            </Link>
            <Link
              to="/categories"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2 text-sm font-medium text-text-primary hover:text-accent"
            >
              Kategori
            </Link>
            {!isAuthenticated && (
              <>
                <Link
                  to="/login"
                  onClick={() => setMobileMenuOpen(false)}
                  className="py-2 text-sm font-medium text-text-primary hover:text-accent"
                >
                  Masuk
                </Link>
                <Link
                  to="/register"
                  onClick={() => setMobileMenuOpen(false)}
                  className="py-2 text-sm font-medium text-text-primary hover:text-accent"
                >
                  Daftar
                </Link>
              </>
            )}
          </nav>
        </div>
      )}
    </header>
  )
}
