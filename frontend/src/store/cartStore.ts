import { create } from 'zustand'
import api from '@/lib/api'

interface CartItem {
  id: number
  product_variant_id: number
  quantity: number
  price_snapshot: number
  variant: {
    id: number
    sku: string
    price: number
    product: {
      id: number
      name: string
      slug: string
      store: {
        id: number
        name: string
        slug: string
      }
    }
    product_images: Array<{ id: number; url: string; is_primary: boolean }>
    attribute_values?: Array<{
      id: number
      value: string
      attribute: { id: number; name: string }
    }>
  }
}

interface Cart {
  id: number
  items: CartItem[]
}

interface CartState {
  cart: Cart | null
  items: CartItem[]
  isLoading: boolean
  itemCount: number
  subtotal: number

  fetchCart: () => Promise<void>
  addItem: (productVariantId: number, quantity: number) => Promise<void>
  updateItem: (cartItemId: number, quantity: number) => Promise<void>
  removeItem: (cartItemId: number) => Promise<void>
  clearCart: () => Promise<void>
  getItemsGroupedByStore: () => Record<string, CartItem[]>
}

export const useCartStore = create<CartState>((set, get) => ({
  cart: null,
  items: [],
  isLoading: false,
  itemCount: 0,
  subtotal: 0,

  fetchCart: async () => {
    try {
      const response = await api.get('/cart')
      const cart = response.data.data
      const items = cart?.items || []
      const itemCount = items.reduce((sum: number, item: CartItem) => sum + item.quantity, 0)
      const subtotal = items.reduce((sum: number, item: CartItem) => sum + item.price_snapshot * item.quantity, 0)
      set({ cart, items, itemCount, subtotal })
    } catch {
      set({ cart: null, items: [], itemCount: 0, subtotal: 0 })
    }
  },

  addItem: async (productVariantId: number, quantity: number) => {
    set({ isLoading: true })
    try {
      await api.post('/cart/items', { product_variant_id: productVariantId, quantity })
      await get().fetchCart()
    } finally {
      set({ isLoading: false })
    }
  },

  updateItem: async (cartItemId: number, quantity: number) => {
    set({ isLoading: true })
    try {
      await api.patch(`/cart/items/${cartItemId}`, { quantity })
      await get().fetchCart()
    } finally {
      set({ isLoading: false })
    }
  },

  removeItem: async (cartItemId: number) => {
    set({ isLoading: true })
    try {
      await api.delete(`/cart/items/${cartItemId}`)
      await get().fetchCart()
    } finally {
      set({ isLoading: false })
    }
  },

  clearCart: async () => {
    set({ isLoading: true })
    try {
      await api.delete('/cart')
      set({ cart: null, items: [], itemCount: 0, subtotal: 0 })
    } finally {
      set({ isLoading: false })
    }
  },

  getItemsGroupedByStore: () => {
    const { items } = get()
    return items.reduce((groups: Record<string, CartItem[]>, item) => {
      const storeName = item.variant.product.store.name
      if (!groups[storeName]) groups[storeName] = []
      groups[storeName].push(item)
      return groups
    }, {})
  },
}))
