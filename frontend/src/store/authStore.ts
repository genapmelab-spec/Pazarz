import { create } from 'zustand'
import api from '@/lib/api'

interface User {
  id: number
  name: string
  email: string
  role: string
  email_verified_at: string | null
}

interface AuthState {
  user: User | null
  token: string | null
  isLoading: boolean
  isAuthenticated: boolean

  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>
  logout: () => Promise<void>
  fetchUser: () => Promise<void>
  setToken: (token: string) => void
  clearAuth: () => void
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: localStorage.getItem('pazarz_token'),
  isLoading: false,
  isAuthenticated: !!localStorage.getItem('pazarz_token'),

  login: async (email: string, password: string) => {
    set({ isLoading: true })
    try {
      const response = await api.post('/auth/login', { email, password })
      const { user, token } = response.data.data
      localStorage.setItem('pazarz_token', token)
      localStorage.setItem('pazarz_user', JSON.stringify(user))
      set({ user, token, isAuthenticated: true, isLoading: false })
    } catch (error) {
      set({ isLoading: false })
      throw error
    }
  },

  register: async (name: string, email: string, password: string, passwordConfirmation: string) => {
    set({ isLoading: true })
    try {
      const response = await api.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      const { user, token } = response.data.data
      localStorage.setItem('pazarz_token', token)
      localStorage.setItem('pazarz_user', JSON.stringify(user))
      set({ user, token, isAuthenticated: true, isLoading: false })
    } catch (error) {
      set({ isLoading: false })
      throw error
    }
  },

  logout: async () => {
    try {
      await api.post('/auth/logout')
    } catch {
      // Ignore errors on logout
    } finally {
      localStorage.removeItem('pazarz_token')
      localStorage.removeItem('pazarz_user')
      set({ user: null, token: null, isAuthenticated: false })
    }
  },

  fetchUser: async () => {
    const token = localStorage.getItem('pazarz_token')
    if (!token) {
      set({ user: null, isAuthenticated: false })
      return
    }
    try {
      const response = await api.get('/auth/me')
      const user = response.data.data.user
      localStorage.setItem('pazarz_user', JSON.stringify(user))
      set({ user, isAuthenticated: true })
    } catch {
      localStorage.removeItem('pazarz_token')
      localStorage.removeItem('pazarz_user')
      set({ user: null, token: null, isAuthenticated: false })
    }
  },

  setToken: (token: string) => {
    localStorage.setItem('pazarz_token', token)
    set({ token, isAuthenticated: true })
  },

  clearAuth: () => {
    localStorage.removeItem('pazarz_token')
    localStorage.removeItem('pazarz_user')
    set({ user: null, token: null, isAuthenticated: false })
  },
}))
