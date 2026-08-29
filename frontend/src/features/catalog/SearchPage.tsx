import { ProductListPage } from './ProductListPage'

// SearchPage uses ProductListPage with the search query pre-set via URL params
// The search query 'q' is read from the URL by ProductListPage
export function SearchPage() {
  return <ProductListPage />
}
