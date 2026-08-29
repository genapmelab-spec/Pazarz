import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { User, MapPin, Heart, Settings, ShoppingBag } from 'lucide-react'
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
