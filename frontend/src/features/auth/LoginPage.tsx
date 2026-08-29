import { useState } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { useCartStore } from '@/store/cartStore'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'

export function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const { login, isLoading } = useAuthStore()
  const { fetchCart } = useCartStore()
  const navigate = useNavigate()
  const location = useLocation()

  const from = (location.state as any)?.from || '/'

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    try {
      await login(email, password)
      await fetchCart()
      navigate(from, { replace: true })
    } catch (err: any) {
      setError(err.response?.data?.error?.message || 'Email atau password salah.')
    }
  }

  return (
    <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
      <div className="w-full max-w-[400px]">
        <div className="text-center mb-8">
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Masuk</h1>
          <p className="text-text-secondary text-sm">
            Belum punya akun?{' '}
            <Link to="/register" className="text-accent font-medium hover:underline">
              Daftar
            </Link>
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <div className="p-3 rounded-[12px] bg-error/10 border border-error/20 text-error text-sm">
              {error}
            </div>
          )}

          <Input
            label="Email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="email@contoh.com"
            required
            autoComplete="email"
          />

          <Input
            label="Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Masukkan password"
            required
            autoComplete="current-password"
          />

          <div className="text-right">
            <Link to="/forgot-password" className="text-sm text-accent hover:underline">
              Lupa password?
            </Link>
          </div>

          <Button type="submit" isLoading={isLoading} className="w-full" size="lg">
            Masuk
          </Button>
        </form>
      </div>
    </div>
  )
}
