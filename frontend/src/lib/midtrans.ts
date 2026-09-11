import api from '@/lib/api'

/**
 * Midtrans Snap helpers.
 *
 * Only the public Client Key ever reaches the browser — the Server Key stays
 * in Laravel. The Snap script URL and client key come from the backend
 * `/orders/{number}/pay` response so sandbox/production stay in sync.
 */

declare global {
  interface Window {
    snap?: {
      pay: (token: string, options: Record<string, unknown>) => void
    }
  }
}

let snapPromise: Promise<NonNullable<Window['snap']>> | null = null

export function loadSnap(clientKey: string, snapJsUrl?: string): Promise<NonNullable<Window['snap']>> {
  if (window.snap) return Promise.resolve(window.snap)
  if (snapPromise) return snapPromise

  const src = snapJsUrl || 'https://app.sandbox.midtrans.com/snap/snap.js'

  snapPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = src
    script.async = true
    script.setAttribute('data-client-key', clientKey)
    script.onload = () => {
      if (window.snap) resolve(window.snap)
      else reject(new Error('Midtrans Snap failed to initialize'))
    }
    script.onerror = () => {
      snapPromise = null
      reject(new Error('Failed to load Midtrans Snap'))
    }
    document.head.appendChild(script)
  })

  return snapPromise
}

export interface SnapCallbacks {
  onSuccess?: (result: unknown) => void
  onPending?: (result: unknown) => void
  onError?: (result: unknown) => void
  onClose?: () => void
}

export interface PaymentInitResult {
  token: string
  client_key: string
  snap_js_url?: string
  order_number: string
  amount: string | number
}

/**
 * Ask Laravel for a Snap token (it creates/retries the Midtrans transaction)
 * and open Snap. Throws when Midtrans is unreachable or the order is not
 * payable.
 */
export async function openPayment(orderNumber: string, callbacks: SnapCallbacks): Promise<void> {
  const res = await api.post(`/orders/${orderNumber}/pay`)
  const data: PaymentInitResult = res.data.data

  const snap = await loadSnap(data.client_key, data.snap_js_url)

  snap.pay(data.token, {
    onSuccess: callbacks.onSuccess,
    onPending: callbacks.onPending,
    onError: callbacks.onError,
    onClose: callbacks.onClose,
  })
}
