import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'
import { MapPin, Plus, Pencil, Trash2, User, Heart, ShoppingBag } from 'lucide-react'
import api from '@/lib/api'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Modal } from '@/components/ui/Modal'
import { cn } from '@/lib/utils'

interface Address {
  id: number
  label: string
  recipient_name: string
  phone: string
  full_address: string
  district: string
  city: string
  province: string
  postal_code: string
  is_default: boolean
}

const NAV_ITEMS = [
  { path: '/account/profile', label: 'Profil', icon: User },
  { path: '/account/addresses', label: 'Alamat', icon: MapPin },
  { path: '/account/wishlist', label: 'Wishlist', icon: Heart },
  { path: '/account/orders', label: 'Pesanan', icon: ShoppingBag },
]

const EMPTY_ADDRESS = {
  label: '', recipient_name: '', phone: '', full_address: '',
  district: '', city: '', province: '', postal_code: '', is_default: false,
}

export function AddressesPage() {
  const location = useLocation()
  const [addresses, setAddresses] = useState<Address[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [showForm, setShowForm] = useState(false)
  const [editingId, setEditingId] = useState<number | null>(null)
  const [form, setForm] = useState(EMPTY_ADDRESS)
  const [isSaving, setIsSaving] = useState(false)

  const fetchAddresses = async () => {
    try {
      const res = await api.get('/addresses')
      setAddresses(res.data.data || [])
    } catch (err) {
      console.error('Failed to fetch addresses:', err)
    } finally {
      setIsLoading(false)
    }
  }

  useEffect(() => { fetchAddresses() }, [])

  const openCreateForm = () => {
    setForm(EMPTY_ADDRESS)
    setEditingId(null)
    setShowForm(true)
  }

  const openEditForm = (addr: Address) => {
    setForm({
      label: addr.label,
      recipient_name: addr.recipient_name,
      phone: addr.phone,
      full_address: addr.full_address,
      district: addr.district,
      city: addr.city,
      province: addr.province,
      postal_code: addr.postal_code,
      is_default: addr.is_default,
    })
    setEditingId(addr.id)
    setShowForm(true)
  }

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsSaving(true)
    try {
      if (editingId) {
        await api.put(`/addresses/${editingId}`, form)
      } else {
        await api.post('/addresses', form)
      }
      await fetchAddresses()
      setShowForm(false)
    } catch (err) {
      console.error('Failed to save address:', err)
    } finally {
      setIsSaving(false)
    }
  }

  const handleDelete = async (id: number) => {
    if (!confirm('Hapus alamat ini?')) return
    try {
      await api.delete(`/addresses/${id}`)
      await fetchAddresses()
    } catch (err) {
      console.error('Failed to delete address:', err)
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
                    isActive ? 'bg-surface text-text-primary' : 'text-text-secondary hover:bg-surface/50 hover:text-text-primary'
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
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-bold tracking-tight">Alamat Saya</h1>
            <Button size="sm" onClick={openCreateForm}>
              <Plus className="w-4 h-4" /> Tambah Alamat
            </Button>
          </div>

          {isLoading ? (
            <div className="space-y-4">
              {[1, 2].map((i) => <div key={i} className="h-32 skeleton rounded-[16px]" />)}
            </div>
          ) : addresses.length === 0 ? (
            <div className="text-center py-16">
              <MapPin className="w-16 h-16 text-text-muted mx-auto mb-4" />
              <h3 className="text-lg font-medium mb-2">Belum ada alamat</h3>
              <p className="text-sm text-text-muted mb-4">Tambahkan alamat pengiriman Anda.</p>
              <Button onClick={openCreateForm}>Tambah Alamat</Button>
            </div>
          ) : (
            <div className="space-y-4">
              {addresses.map((addr) => (
                <div key={addr.id} className="p-5 rounded-[16px] border border-divider">
                  <div className="flex items-start justify-between">
                    <div>
                      <div className="flex items-center gap-2 mb-1">
                        <span className="text-sm font-semibold">{addr.label}</span>
                        {addr.is_default && (
                          <span className="px-2 py-0.5 rounded-full bg-accent/10 text-accent text-xs">Utama</span>
                        )}
                      </div>
                      <p className="text-sm text-text-primary">{addr.recipient_name} · {addr.phone}</p>
                      <p className="text-sm text-text-secondary mt-1">
                        {addr.full_address}, {addr.district || ''}, {addr.city}, {addr.province} {addr.postal_code}
                      </p>
                    </div>
                    <div className="flex gap-1">
                      <button
                        onClick={() => openEditForm(addr)}
                        className="p-2 rounded-full hover:bg-surface transition-colors"
                      >
                        <Pencil className="w-4 h-4 text-text-muted" />
                      </button>
                      <button
                        onClick={() => handleDelete(addr.id)}
                        className="p-2 rounded-full hover:bg-error/5 transition-colors"
                      >
                        <Trash2 className="w-4 h-4 text-text-muted hover:text-error" />
                      </button>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      {/* Modal Form */}
      <Modal
        isOpen={showForm}
        onClose={() => setShowForm(false)}
        title={editingId ? 'Edit Alamat' : 'Tambah Alamat'}
      >
        <form onSubmit={handleSave} className="space-y-4">
          <div className="grid grid-cols-2 gap-3">
            <Input placeholder="Label (rumah/kantor)" value={form.label} onChange={(e) => setForm({ ...form, label: e.target.value })} required />
            <Input placeholder="Nama penerima" value={form.recipient_name} onChange={(e) => setForm({ ...form, recipient_name: e.target.value })} required />
          </div>
          <Input placeholder="No. telepon" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} required />
          <Input placeholder="Alamat lengkap" value={form.full_address} onChange={(e) => setForm({ ...form, full_address: e.target.value })} required />
          <div className="grid grid-cols-4 gap-3">
            <Input placeholder="Kecamatan" value={form.district} onChange={(e) => setForm({ ...form, district: e.target.value })} required />
            <Input placeholder="Kota" value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} required />
            <Input placeholder="Provinsi" value={form.province} onChange={(e) => setForm({ ...form, province: e.target.value })} required />
            <Input placeholder="Kode pos" value={form.postal_code} onChange={(e) => setForm({ ...form, postal_code: e.target.value })} required />
          </div>
          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              id="is_default"
              checked={form.is_default}
              onChange={(e) => setForm({ ...form, is_default: e.target.checked })}
              className="w-4 h-4 rounded border-border accent-primary"
            />
            <label htmlFor="is_default" className="text-sm text-text-secondary">Jadikan alamat utama</label>
          </div>
          <div className="flex gap-2 pt-2">
            <Button type="submit" isLoading={isSaving} className="flex-1">
              Simpan
            </Button>
            <Button type="button" variant="ghost" onClick={() => setShowForm(false)}>
              Batal
            </Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
