import { useEffect, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { MapPin, Truck, Plus, Package } from 'lucide-react'
import api from '@/lib/api'
import { useCartStore } from '@/store/cartStore'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { formatPrice, cn } from '@/lib/utils'

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

export function CheckoutPage() {
  const { items, subtotal, fetchCart } = useCartStore()
  const navigate = useNavigate()
  const [addresses, setAddresses] = useState<Address[]>([])
  const [selectedAddressId, setSelectedAddressId] = useState<number | null>(null)
  const [couponCode, setCouponCode] = useState('')
  const [couponError, setCouponError] = useState('')
  const [isPlacing, setIsPlacing] = useState(false)
  const [showAddAddress, setShowAddAddress] = useState(false)


  // New address form
  const [newAddress, setNewAddress] = useState({
    label: '', recipient_name: '', phone: '', full_address: '',
    district: '', city: '', province: '', postal_code: '',
  })

  useEffect(() => {
    fetchCart()
    fetchAddresses()
  }, [])

  useEffect(() => {
    if (items.length === 0) {
      navigate('/cart')
    }
  }, [items, navigate])

  const fetchAddresses = async () => {
    try {
      const res = await api.get('/addresses')
      const data = res.data.data || []
      setAddresses(data)
      const defaultAddr = data.find((a: Address) => a.is_default)
      if (defaultAddr) setSelectedAddressId(defaultAddr.id)
      else if (data.length > 0) setSelectedAddressId(data[0].id)
    } catch (err) {
      console.error('Failed to fetch addresses:', err)
    }
  }

  const handleAddAddress = async (e: React.FormEvent) => {
    e.preventDefault()
    try {
      await api.post('/addresses', newAddress)
      await fetchAddresses()
      setShowAddAddress(false)
      setNewAddress({ label: '', recipient_name: '', phone: '', full_address: '', district: '', city: '', province: '', postal_code: '' })
    } catch (err) {
      console.error('Failed to add address:', err)
    }
  }

  const handlePlaceOrder = async () => {
    if (!selectedAddressId) return
    setIsPlacing(true)
    try {
      // Build shipping methods from grouped items (default free shipping per store)
      const shippingMethods = Object.values(groupedItems).map(({ store }) => ({
        store_id: store.id,
        courier: 'jne',
        cost: 0,
      }))
      const response = await api.post('/checkout', {
        shipping_address_id: selectedAddressId,
        shipping_methods: shippingMethods,
        coupon_code: couponCode || undefined,
      })
      const order = response.data.data?.order
      navigate(`/payment-status?order=${order?.order_number || ''}`, { replace: true })
    } catch (err: any) {
      if (err.response?.status === 409) {
        alert('Beberapa item tidak tersedia. Silakan review keranjang Anda.')
        navigate('/cart')
      } else if (err.response?.status === 422) {
        setCouponError(err.response?.data?.error?.message || 'Kupon tidak valid')
      }
    } finally {
      setIsPlacing(false)
    }
  }

  // Group items by store for display
  const groupedItems = items.reduce((groups, item) => {
    const storeName = item.variant.product.store.name
    if (!groups[storeName]) groups[storeName] = { store: item.variant.product.store, items: [] }
    groups[storeName].items.push(item)
    return groups
  }, {} as Record<string, { store: any; items: typeof items }>)

  if (items.length === 0) return null

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <h1 className="text-[32px] font-bold tracking-tight mb-8">Checkout</h1>

      <div className="flex flex-col lg:flex-row gap-8">
        {/* Main */}
        <div className="flex-1 space-y-6">
          {/* Step: Address */}
          <section className="rounded-[16px] border border-divider p-6">
            <div className="flex items-center gap-3 mb-4">
              <div className={cn('w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold', 'bg-primary text-white')}>
                1
              </div>
              <h2 className="text-lg font-semibold">Alamat Pengiriman</h2>
            </div>

            {addresses.length === 0 ? (
              <div className="text-center py-8">
                <MapPin className="w-10 h-10 text-text-muted mx-auto mb-3" />
                <p className="text-sm text-text-muted mb-4">Belum ada alamat tersimpan</p>
                <Button variant="secondary" onClick={() => setShowAddAddress(true)}>
                  <Plus className="w-4 h-4" /> Tambah Alamat
                </Button>
              </div>
            ) : (
              <div className="space-y-3">
                {addresses.map((address) => (
                  <button
                    key={address.id}
                    onClick={() => setSelectedAddressId(address.id)}
                    className={cn(
                      'w-full text-left p-4 rounded-[12px] border transition-all',
                      selectedAddressId === address.id
                        ? 'border-accent bg-accent/5'
                        : 'border-divider hover:border-text-muted'
                    )}
                  >
                    <div className="flex items-start justify-between">
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                          <span className="text-sm font-medium">{address.label}</span>
                          {address.is_default && (
                            <span className="px-2 py-0.5 rounded-full bg-accent/10 text-accent text-xs">Utama</span>
                          )}
                        </div>
                        <p className="text-sm text-text-secondary">{address.recipient_name} · {address.phone}</p>
                        <p className="text-sm text-text-muted mt-1">
                          {address.full_address}, {address.district || ''}, {address.city}, {address.province} {address.postal_code}
                        </p>
                      </div>
                      <div className={cn(
                        'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-1',
                        selectedAddressId === address.id ? 'border-accent bg-accent' : 'border-border'
                      )}>
                        {selectedAddressId === address.id && (
                          <div className="w-2 h-2 rounded-full bg-white" />
                        )}
                      </div>
                    </div>
                  </button>
                ))}
                <button
                  onClick={() => setShowAddAddress(true)}
                  className="w-full p-3 rounded-[12px] border border-dashed border-border text-sm text-text-muted hover:border-text-muted hover:text-text-secondary transition-colors flex items-center justify-center gap-2"
                >
                  <Plus className="w-4 h-4" />
                  Tambah Alamat Baru
                </button>
              </div>
            )}

            {/* Add Address Form */}
            {showAddAddress && (
              <div className="mt-4 p-4 rounded-[12px] bg-surface">
                <h3 className="text-sm font-semibold mb-3">Alamat Baru</h3>
                <form onSubmit={handleAddAddress} className="space-y-3">
                  <div className="grid grid-cols-2 gap-3">
                    <Input placeholder="Label (rumah/kantor)" value={newAddress.label} onChange={(e) => setNewAddress({ ...newAddress, label: e.target.value })} required />
                    <Input placeholder="Nama penerima" value={newAddress.recipient_name} onChange={(e) => setNewAddress({ ...newAddress, recipient_name: e.target.value })} required />
                  </div>
                  <Input placeholder="No. telepon" value={newAddress.phone} onChange={(e) => setNewAddress({ ...newAddress, phone: e.target.value })} required />
                  <Input placeholder="Alamat lengkap" value={newAddress.full_address} onChange={(e) => setNewAddress({ ...newAddress, full_address: e.target.value })} required />
                  <div className="grid grid-cols-4 gap-3">
                    <Input placeholder="Kecamatan" value={newAddress.district} onChange={(e) => setNewAddress({ ...newAddress, district: e.target.value })} required />
                    <Input placeholder="Kota" value={newAddress.city} onChange={(e) => setNewAddress({ ...newAddress, city: e.target.value })} required />
                    <Input placeholder="Provinsi" value={newAddress.province} onChange={(e) => setNewAddress({ ...newAddress, province: e.target.value })} required />
                    <Input placeholder="Kode pos" value={newAddress.postal_code} onChange={(e) => setNewAddress({ ...newAddress, postal_code: e.target.value })} required />
                  </div>
                  <div className="flex gap-2">
                    <Button type="submit" size="sm">Simpan</Button>
                    <Button type="button" variant="ghost" size="sm" onClick={() => setShowAddAddress(false)}>Batal</Button>
                  </div>
                </form>
              </div>
            )}
          </section>

          {/* Items by Store */}
          {Object.entries(groupedItems).map(([storeName, { store, items: storeItems }]) => (
            <section key={storeName} className="rounded-[16px] border border-divider p-6">
              <div className="flex items-center gap-2 mb-4">
                <Package className="w-4 h-4 text-text-muted" />
                <h3 className="text-sm font-semibold">{storeName}</h3>
              </div>
              <div className="space-y-3">
                {storeItems.map((item) => {
                  const imageUrl = item.variant.product_images?.find((img: any) => img.is_primary)?.url ||
                    item.variant.product_images?.[0]?.url
                  return (
                    <div key={item.id} className="flex items-center gap-3">
                      <div className="w-12 h-12 rounded-[8px] bg-surface overflow-hidden flex-shrink-0">
                        {imageUrl && <img src={imageUrl} alt="" className="w-full h-full object-cover" />}
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm line-clamp-1">{item.variant.product.name}</p>
                        <p className="text-xs text-text-muted">
                          {item.variant.attribute_values?.map((av: any) => av.value).join(' / ')} × {item.quantity}
                        </p>
                      </div>
                      <span className="text-sm font-medium">{formatPrice(item.price_snapshot * item.quantity)}</span>
                    </div>
                  )
                })}
              </div>
              <div className="border-t border-divider mt-3 pt-3 flex items-center gap-2 text-sm text-text-muted">
                <Truck className="w-4 h-4" />
                Pengiriman estimasi: Rp 0 - Rp 50.000
              </div>
            </section>
          ))}
        </div>

        {/* Summary Sidebar */}
        <div className="w-full lg:w-[360px] flex-shrink-0">
          <div className="sticky top-24 bg-white rounded-[16px] border border-divider p-6 space-y-4">
            <h3 className="text-lg font-semibold">Ringkasan Belanja</h3>

            <div className="space-y-2 text-sm">
              <div className="flex justify-between">
                <span className="text-text-secondary">Subtotal</span>
                <span className="font-medium">{formatPrice(subtotal)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-text-secondary">Pengiriman</span>
                <span className="text-text-muted">Dihitung berikutnya</span>
              </div>
            </div>

            {/* Coupon */}
            <div>
              <div className="flex gap-2">
                <Input
                  placeholder="Kode kupon"
                  value={couponCode}
                  onChange={(e) => { setCouponCode(e.target.value); setCouponError('') }}
                  error={couponError}
                />
                <Button variant="secondary" size="sm" className="flex-shrink-0 mt-0.5">Terapkan</Button>
              </div>
            </div>

            <div className="border-t border-divider pt-4">
              <div className="flex justify-between items-baseline">
                <span className="font-semibold">Total</span>
                <span className="text-xl font-bold">{formatPrice(subtotal)}</span>
              </div>
            </div>

            <Button
              onClick={handlePlaceOrder}
              isLoading={isPlacing}
              disabled={!selectedAddressId}
              className="w-full"
              size="lg"
            >
              Bayar Sekarang
            </Button>

            <p className="text-xs text-text-muted text-center">
              Dengan melanjutkan, Anda menyetujui syarat dan ketentuan yang berlaku.
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
