// API helper for authenticated requests
const API_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000/api'

export function getAuthHeaders() {
  const token = localStorage.getItem('token')
  const headers = {
    'Content-Type': 'application/json'
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  return headers
}

export async function fetchWithAuth(url, options = {}) {
  const headers = {
    ...getAuthHeaders(),
    ...(options.headers || {})
  }

  const response = await fetch(url, {
    ...options,
    headers
  })

  // If unauthorized, redirect to login
  if (response.status === 401) {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    window.location.href = '/'
  }

  return response
}

export function getApiUrl() {
  return API_URL
}
