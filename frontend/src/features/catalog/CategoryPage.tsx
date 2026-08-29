import { ProductListPage } from './ProductListPage'

// CategoryPage uses ProductListPage with the category filter pre-set via URL params
// The category slug is read from the URL by ProductListPage
export function CategoryPage() {
  return <ProductListPage />
}
