import { useState, useEffect } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import api from '@/lib/api'

export function BecomeSellerPage() {
  const { user, isAuthenticated } = useAuthStore()
  const navigate = useNavigate()

  const [businessName, setBusinessName] = useState('')
  const [businessType, setBusinessType] = useState('')
  const [taxId, setTaxId] = useState('')
  const [error, setError] = useState('')
  const [isLoading, setIsLoading] = useState(false)
  const [sellerStatus, setSellerStatus] = useState<any>(null)
  const [checking, setChecking] = useState(true)

  // Check if already a seller
  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/login', { state: { from: '/become-seller' } })
      return
    }

    api.get('/seller-status')
      .then(res => {
        setSellerStatus(res.data.data)
        setChecking(false)
      })
      .catch(() => setChecking(false))
  }, [isAuthenticated, navigate])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setIsLoading(true)

    try {
      await api.post('/become-seller', {
        business_name: businessName,
        business_type: businessType || undefined,
        tax_id: taxId || undefined,
      })
      // Reload to show pending status
      window.location.href = '/become-seller'
    } catch (err: any) {
      setError(err.response?.data?.error?.message || 'Gagal mengirim aplikasi.')
    } finally {
      setIsLoading(false)
    }
  }

  if (checking) {
    return (
      <div className="min-h-[calc(100vh-72px)] flex items-center justify-center">
        <div className="text-text-secondary text-sm">Memuat...</div>
      </div>
    )
  }

  // Already a verified seller
  if (sellerStatus?.is_seller && sellerStatus?.status === 'verified') {
    return (
      <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
        <div className="w-full max-w-[440px] text-center">
          <div className="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 className="text-[28px] font-bold tracking-tight mb-2">Kamu Sudah Seller!</h1>
          <p className="text-text-secondary text-sm mb-6">
            Akun seller kamu sudah aktif. Kelola toko kamu dari dashboard seller.
          </p>
          <a
            href="http://127.0.0.1:8000/seller"
            className="inline-block bg-black text-white px-6 py-3 rounded-full font-medium hover:bg-gray-800 transition"
          >
            Buka Dashboard Seller →
          </a>
        </div>
      </div>
    )
  }

  // Pending approval
  if (sellerStatus?.is_seller && sellerStatus?.status === 'pending') {
    return (
      <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
        <div className="w-full max-w-[440px] text-center">
          <div className="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h1 className="text-[28px] font-bold tracking-tight mb-2">Menunggu Persetujuan</h1>
          <p className="text-text-secondary text-sm mb-4">
            Aplikasi seller kamu sedang dalam proses review oleh admin.
          </p>
          <div className="bg-surface rounded-[12px] p-4 mb-6">
            <p className="text-sm text-text-secondary">
              <strong>Status:</strong>{' '}
              <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                Pending Review
              </span>
            </p>
            <p className="text-xs text-text-tertiary mt-2">
              Biasanya proses review memakan waktu 1-2 hari kerja.
            </p>
          </div>
          <Link to="/" className="text-sm text-accent hover:underline">
            ← Kembali ke Beranda
          </Link>
        </div>
      </div>
    )
  }

  // Rejected
  if (sellerStatus?.is_seller && sellerStatus?.status === 'rejected') {
    return (
      <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
        <div className="w-full max-w-[440px] text-center">
          <div className="w-16 h-16 bg-error/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <h1 className="text-[28px] font-bold tracking-tight mb-2">Aplikasi Ditolak</h1>
          <p className="text-text-secondary text-sm mb-6">
            Maaf, aplikasi seller kamu tidak disetujui. Silakan hubungi support untuk informasi lebih lanjut.
          </p>
          <Link to="/" className="text-sm text-accent hover:underline">
            ← Kembali ke Beranda
          </Link>
        </div>
      </div>
    )
  }

  // Not a seller yet — show application form
  return (
    <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
      <div className="w-full max-w-[440px]">
        <div className="text-center mb-8">
          <div className="w-16 h-16 bg-surface rounded-full flex items-center justify-center mx-auto mb-4">
            <svg className="w-8 h-8 text-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Jadi Seller di Pazarz</h1>
          <p className="text-text-secondary text-sm">
            Mulai jual produk kamu ke ribuan pembeli. Isi form di bawah untuk mendaftar.
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <div className="p-3 rounded-[12px] bg-error/10 border border-error/20 text-error text-sm">
              {error}
            </div>
          )}

          <div>
            <label className="block text-sm font-medium text-text-primary mb-1.5">Nama Bisnis *</label>
            <input
              type="text"
              value={businessName}
              onChange={(e) => setBusinessName(e.target.value)}
              placeholder="Contoh: Urban Streetwear"
              required
              className="w-full px-4 py-3 border border-border rounded-[12px] focus:ring-2 focus:ring-accent focus:border-transparent outline-none text-sm"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-text-primary mb-1.5">Jenis Bisnis</label>
            <input
              type="text"
              value={businessType}
              onChange={(e) => setBusinessType(e.target.value)}
              placeholder="Contoh: Fashion, Aksesoris"
              className="w-full px-4 py-3 border border-border rounded-[12px] focus:ring-2 focus:ring-accent focus:border-transparent outline-none text-sm"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-text-primary mb-1.5">NPWP (Opsional)</label>
            <input
              type="text"
              value={taxId}
              onChange={(e) => setTaxId(e.target.value)}
              placeholder="Nomor NPWP"
              className="w-full px-4 py-3 border border-border rounded-[12px] focus:ring-2 focus:ring-accent focus:border-transparent outline-none text-sm"
            />
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full bg-black text-white py-3 rounded-full font-medium hover:bg-gray-800 transition disabled:opacity-50 mt-4"
          >
            {isLoading ? 'Mengirim...' : 'Kirim Aplikasi'}
          </button>
        </form>

        <p className="text-center text-xs text-text-tertiary mt-6">
          Dengan mendaftar, kamu setuju dengan syarat dan ketentuan Pazarz untuk seller.
        </p>
      </div>
    </div>
  )
}
