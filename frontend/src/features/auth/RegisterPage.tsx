import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'

export function RegisterPage() {
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [generalError, setGeneralError] = useState('')
  const { register, isLoading } = useAuthStore()
  const navigate = useNavigate()

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})
    setGeneralError('')

    if (password !== passwordConfirmation) {
      setErrors({ password_confirmation: 'Password tidak cocok.' })
      return
    }

    try {
      await register(name, email, password, passwordConfirmation)
      navigate('/')
    } catch (err: any) {
      if (err.response?.status === 422) {
        const validationErrors = err.response.data.errors || {}
        const fieldErrors: Record<string, string> = {}
        Object.entries(validationErrors).forEach(([key, messages]) => {
          fieldErrors[key] = (messages as string[])[0]
        })
        setErrors(fieldErrors)
      } else {
        setGeneralError(err.response?.data?.error?.message || 'Gagal mendaftar. Silakan coba lagi.')
      }
    }
  }

  return (
    <div className="min-h-[calc(100vh-72px)] flex items-center justify-center px-5 py-12">
      <div className="w-full max-w-[400px]">
        <div className="text-center mb-8">
          <h1 className="text-[32px] font-bold tracking-tight mb-2">Daftar</h1>
          <p className="text-text-secondary text-sm">
            Sudah punya akun?{' '}
            <Link to="/login" className="text-accent font-medium hover:underline">
              Masuk
            </Link>
          </p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-4">
          {generalError && (
            <div className="p-3 rounded-[12px] bg-error/10 border border-error/20 text-error text-sm">
              {generalError}
            </div>
          )}

          <Input
            label="Nama"
            type="text"
            value={name}
            onChange={(e) => setName(e.target.value)}
            placeholder="Nama lengkap"
            error={errors.name}
            required
            autoComplete="name"
          />

          <Input
            label="Email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="email@contoh.com"
            error={errors.email}
            required
            autoComplete="email"
          />

          <Input
            label="Password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Minimal 8 karakter"
            error={errors.password}
            required
            autoComplete="new-password"
          />

          <Input
            label="Konfirmasi Password"
            type="password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            placeholder="Ulangi password"
            error={errors.password_confirmation}
            required
            autoComplete="new-password"
          />

          <Button type="submit" isLoading={isLoading} className="w-full" size="lg">
            Daftar
          </Button>
        </form>
      </div>
    </div>
  )
}
