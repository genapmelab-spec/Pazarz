import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import api from '@/lib/api'


interface Category {
  id: number
  name: string
  slug: string
  description?: string
  products_count?: number
  children?: Category[]
}

export function CategoriesPage() {
  const [categories, setCategories] = useState<Category[]>([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const res = await api.get('/categories')
        setCategories(res.data.data || [])
      } catch (err) {
        console.error('Failed to fetch categories:', err)
      } finally {
        setIsLoading(false)
      }
    }
    fetchCategories()
  }, [])

  return (
    <div className="max-w-[1280px] mx-auto px-5 lg:px-16 py-6 lg:py-8">
      <nav className="text-sm text-text-muted mb-4">
        <Link to="/" className="hover:text-accent transition-colors">Beranda</Link>
        <span className="mx-2">/</span>
        <span className="text-text-primary font-medium">Kategori</span>
      </nav>

      <h1 className="text-[32px] font-bold tracking-tight mb-8">Kategori</h1>

      {isLoading ? (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {[1, 2, 3, 4, 5, 6].map((i) => (
            <div key={i} className="h-40 skeleton rounded-[16px]" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
          {categories.map((category) => (
            <Link
              key={category.id}
              to={`/categories/${category.slug}`}
              className="group p-6 rounded-[16px] bg-surface hover:bg-surface/80 transition-all hover:shadow-elevation-1"
            >
              <div className="w-14 h-14 rounded-full bg-primary/5 flex items-center justify-center mb-4 group-hover:bg-primary/10 transition-colors">
                <span className="text-xl font-bold text-primary">{category.name[0]}</span>
              </div>
              <h3 className="text-base font-semibold text-text-primary mb-1">{category.name}</h3>
              {category.description && (
                <p className="text-sm text-text-muted line-clamp-2">{category.description}</p>
              )}
              {category.products_count != null && (
                <p className="text-xs text-text-muted mt-2">{category.products_count} produk</p>
              )}
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}
