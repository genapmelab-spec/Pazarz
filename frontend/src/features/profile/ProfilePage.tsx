import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { User, MapPin, Heart, Settings, ShoppingBag, Store, AlertTriangle, ExternalLink } from 'lucide-react'
import api from '@/lib/api'
import { useAuthStore } from '@/store/authStore'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { cn } from '@/lib/utils'

const NAV_ITEMS = [
  { path: '/account/profile', label: 'Profil', icon: User },
  { path: '/account/addresses', label: 'Alamat', icon: MapPin },
  { path: '/account/wishlist', label: 'Wishlist', icon: Heart },
  { path: '/account/orders', label: 'Pesanan', icon: ShoppingBag },
]

export function ProfilePage() {
  const { user } = useAuthStore()
  const location = useLocation()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [phone, setPhone] = useState('')
  const [isSaving, setIsSaving] = useState(false)
  const [success, setSuccess] = useState(false)
  const [sellerStatus, setSellerStatus] = useState<{ is_seller: boolean; status: string | null; business_name?: string } | null>(null)

  const BLADE_URL = 'http://127.0.0.1:8000'

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        const res = await api.get('/profile')
        const profile = res.data.data
        setName(profile.name || '')
        setEmail(profile.email || '')
        setPhone(profile.phone || '')
      } catch {
        if (user) {
          setName(user.name)
          setEmail(user.email)
        }
      }
    }
    fetchProfile()
    // Fetch seller status
    api.get('/seller-status').then(res => setSellerStatus(res.data.data)).catch(() => {})
  }, [user])

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSaving(true)
    setSuccess(false)
    try {
      await api.put('/profile', { name, phone })
      setSuccess(true)
      setTimeout(() => setSuccess(false), 3000)
    } catch (err) {
      console.error('Failed to update profile:', err)
    } finally {
      setIsSaving(false)
    }
  }

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <div className="flex flex-col lg:flex-row gap-8">
        {/* Sidebar */}
        <aside className="w-full lg:w-[260px] flex-shrink-0">
          <nav className="flex lg:flex-col gap-2 overflow-x-auto pb-2 lg:pb-0">
            {NAV_ITEMS.map((item) => {
              const Icon = item.icon
              const isActive = location.pathname === item.path
              return (
                <Link
                  key={item.path}
                  to={item.path}
                  className={cn(
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium whitespace-nowrap transition-colors',
                    isActive
                      ? 'bg-surface text-text-primary'
                      : 'text-text-secondary hover:bg-surface/50 hover:text-text-primary'
                  )}
                >
                  <Icon className="w-4 h-4" />
                  {item.label}
                </Link>
              )
            })}
          </nav>
        </aside>

        {/* Content */}
        <div className="flex-1">
          <h1 className="text-2xl font-bold tracking-tight mb-6">Profil Saya</h1>

          {/* Seller Dashboard Link */}
          {sellerStatus?.is_seller && sellerStatus?.status === 'verified' && (
            <div className="mb-6 bg-white rounded-2xl border border-divider p-5">
              <div className="flex items-start gap-4">
                <div className="w-10 h-10 bg-accent/10 rounded-xl flex items-center justify-center flex-shrink-0">
                  <Store className="w-5 h-5 text-accent" />
                </div>
                <div className="flex-1">
                  <h3 className="text-sm font-semibold text-text-primary">Dashboard Seller</h3>
                  <p className="text-xs text-text-secondary mt-0.5">
                    Kelola produk, pesanan, dan inventaris toko kamu.
                  </p>
                  <div className="flex items-center gap-2 mt-3">
                    <a
                      href={`${BLADE_URL}/dashboard/login`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white rounded-xl text-xs font-medium hover:bg-primary/90 transition-colors"
                    >
                      Buka Dashboard
                      <ExternalLink className="w-3 h-3" />
                    </a>
                  </div>
                  <div className="flex items-start gap-2 mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <AlertTriangle className="w-4 h-4 text-yellow-600 mt-0.5 flex-shrink-0" />
                    <p className="text-xs text-yellow-700">
                      <strong>Jangan bagikan</strong> link dashboard ini kepada orang lain. Dashboard hanya untuk kamu sebagai seller terverifikasi.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {sellerStatus?.is_seller && sellerStatus?.status === 'pending' && (
            <div className="mb-6 bg-white rounded-2xl border border-divider p-5">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                  <span className="w-2.5 h-2.5 bg-yellow-500 rounded-full" />
                </div>
                <div>
                  <h3 className="text-sm font-semibold text-text-primary">Aplikasi Seller Pending</h3>
                  <p className="text-xs text-text-secondary">Menunggu persetujuan admin. Kamu akan dihubungi via email.</p>
                </div>
              </div>
            </div>
          )}

          <form onSubmit={handleSave} className="max-w-[480px] space-y-4">
            {success && (
              <div className="p-3 rounded-[12px] bg-success/10 border border-success/20 text-success text-sm">
                Profil berhasil disimpan.
              </div>
            )}

            <Input
              label="Nama"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
            <Input
              label="Email"
              value={email}
              disabled
              className="opacity-60"
            />
            <Input
              label="Telepon"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              placeholder="Nomor telepon"
            />

            <Button type="submit" isLoading={isSaving}>
              Simpan Perubahan
            </Button>
          </form>
        </div>
      </div>
    </div>
  )
}
