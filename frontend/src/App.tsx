import { useEffect } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { useCartStore } from '@/store/cartStore'
import { MainLayout } from '@/components/layout'

// Pages
import { HomePage } from '@/features/catalog/HomePage'
import { ProductListPage } from '@/features/catalog/ProductListPage'
import { ProductDetailPage } from '@/features/catalog/ProductDetailPage'
import { CategoryPage } from '@/features/catalog/CategoryPage'
import { CategoriesPage } from '@/features/catalog/CategoriesPage'
import { SearchPage } from '@/features/catalog/SearchPage'
import { StoreDetailPage } from '@/features/catalog/StoreDetailPage'
import { LoginPage } from '@/features/auth/LoginPage'
import { RegisterPage } from '@/features/auth/RegisterPage'
import { CartPage } from '@/features/cart/CartPage'
import { CheckoutPage } from '@/features/checkout/CheckoutPage'
import { PaymentStatusPage } from '@/features/checkout/PaymentStatusPage'
import { OrderListPage } from '@/features/orders/OrderListPage'
import { OrderDetailPage } from '@/features/orders/OrderDetailPage'
import { ProfilePage } from '@/features/profile/ProfilePage'
import { AddressesPage } from '@/features/profile/AddressesPage'
import { WishlistPage } from '@/features/profile/WishlistPage'

function App() {
  const { fetchUser, isAuthenticated } = useAuthStore()
  const { fetchCart } = useCartStore()

  useEffect(() => {
    if (isAuthenticated) {
      fetchUser()
      fetchCart()
    }
  }, [isAuthenticated, fetchUser, fetchCart])

  return (
    <BrowserRouter>
      <Routes>
        <Route element={<MainLayout />}>
          {/* Public Routes */}
          <Route path="/" element={<HomePage />} />
          <Route path="/products" element={<ProductListPage />} />
          <Route path="/products/:slug" element={<ProductDetailPage />} />
          <Route path="/categories" element={<CategoriesPage />} />
          <Route path="/categories/:slug" element={<CategoryPage />} />
          <Route path="/search" element={<SearchPage />} />
          <Route path="/stores/:slug" element={<StoreDetailPage />} />

          {/* Auth Routes */}
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          {/* Cart */}
          <Route path="/cart" element={<CartPage />} />

          {/* Protected Routes */}
          <Route path="/checkout" element={<CheckoutPage />} />
          <Route path="/payment-status" element={<PaymentStatusPage />} />

          {/* Account Routes */}
          <Route path="/account/profile" element={<ProfilePage />} />
          <Route path="/account/addresses" element={<AddressesPage />} />
          <Route path="/account/wishlist" element={<WishlistPage />} />
          <Route path="/account/orders" element={<OrderListPage />} />
          <Route path="/account/orders/:id" element={<OrderDetailPage />} />

          {/* Catch all */}
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
