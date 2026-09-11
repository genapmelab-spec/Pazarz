import { useEffect } from 'react'
import { BrowserRouter, Routes, Route, Navigate, useLocation } from 'react-router-dom'
import { AnimatePresence } from 'framer-motion'
import { useAuthStore } from '@/store/authStore'
import { useCartStore } from '@/store/cartStore'
import { MainLayout } from '@/components/layout'
import { PageTransition } from '@/components/ui/PageTransition'
import Lenis from 'lenis'

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
import { BecomeSellerPage } from '@/features/seller/BecomeSellerPage'

// Lenis smooth scroll setup
function SmoothScrollProvider({ children }: { children: React.ReactNode }) {
  const location = useLocation()

  useEffect(() => {
    const lenis = new Lenis({
      duration: 1.2,
      easing: (t: number) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      touchMultiplier: 2,
      infinite: false,
    })

    function raf(time: number) {
      lenis.raf(time)
      requestAnimationFrame(raf)
    }
    requestAnimationFrame(raf)

    return () => {
      lenis.destroy()
    }
  }, [])

  // Scroll to top on route change
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' })
  }, [location.pathname])

  return <>{children}</>
}

function AnimatedRoutes() {
  const location = useLocation()

  return (
    <AnimatePresence mode="wait">
      <Routes location={location} key={location.pathname}>
        <Route element={<MainLayout />}>
          <Route path="/" element={<PageTransition><HomePage /></PageTransition>} />
          <Route path="/products" element={<PageTransition><ProductListPage /></PageTransition>} />
          <Route path="/products/:slug" element={<PageTransition><ProductDetailPage /></PageTransition>} />
          <Route path="/categories" element={<PageTransition><CategoriesPage /></PageTransition>} />
          <Route path="/categories/:slug" element={<PageTransition><CategoryPage /></PageTransition>} />
          <Route path="/search" element={<PageTransition><SearchPage /></PageTransition>} />
          <Route path="/stores/:slug" element={<PageTransition><StoreDetailPage /></PageTransition>} />

          <Route path="/login" element={<PageTransition><LoginPage /></PageTransition>} />
          <Route path="/register" element={<PageTransition><RegisterPage /></PageTransition>} />

          <Route path="/cart" element={<PageTransition><CartPage /></PageTransition>} />

          <Route path="/checkout" element={<PageTransition><CheckoutPage /></PageTransition>} />
          <Route path="/payment-status" element={<PageTransition><PaymentStatusPage /></PageTransition>} />

          <Route path="/become-seller" element={<PageTransition><BecomeSellerPage /></PageTransition>} />

          <Route path="/account/profile" element={<PageTransition><ProfilePage /></PageTransition>} />
          <Route path="/account/addresses" element={<PageTransition><AddressesPage /></PageTransition>} />
          <Route path="/account/wishlist" element={<PageTransition><WishlistPage /></PageTransition>} />
          <Route path="/account/orders" element={<PageTransition><OrderListPage /></PageTransition>} />
          <Route path="/account/orders/:id" element={<PageTransition><OrderDetailPage /></PageTransition>} />

          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </AnimatePresence>
  )
}

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
      <SmoothScrollProvider>
        <AnimatedRoutes />
      </SmoothScrollProvider>
    </BrowserRouter>
  )
}

export default App
