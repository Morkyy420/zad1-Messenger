export const themes = {
  darkModern: {
    name: 'Dark Modern',
    icon: '🌙',
    description: 'Nowoczesny ciemny motyw',
    class: 'dark-modern',
    colors: {
      primary: '#3b82f6',
      primaryDark: '#2563eb',
      primaryLight: '#60a5fa',
      primaryLighter: '#93c5fd',
      background1: '#0f172a',
      background2: '#1e293b',
      background3: '#020617',
      cardBg: 'rgba(30, 41, 59, 0.8)',
      border: 'rgba(71, 85, 105, 0.5)',
      text: '#f1f5f9',
      textLight: '#94a3b8',
      success: '#10b981',
      error: '#ef4444'
    }
  },
  lightModern: {
    name: 'Light Modern',
    icon: '☀️',
    description: 'Minimalistyczny jasny motyw',
    class: 'light-modern',
    colors: {
      primary: '#3b82f6',
      primaryDark: '#2563eb',
      primaryLight: '#60a5fa',
      primaryLighter: '#93c5fd',
      background1: '#ffffff',
      background2: '#f8fafc',
      background3: '#f1f5f9',
      cardBg: 'rgba(255, 255, 255, 0.9)',
      border: 'rgba(226, 232, 240, 0.8)',
      text: '#0f172a',
      textLight: '#64748b',
      success: '#10b981',
      error: '#ef4444'
    }
  },
  feminine: {
    name: 'Feminine Garden',
    icon: '🌸',
    description: 'Delikatny jasny motyw z kwiatkami',
    class: 'feminine',
    colors: {
      primary: '#ec4899',
      primaryDark: '#db2777',
      primaryLight: '#f472b6',
      primaryLighter: '#f9a8d4',
      background1: '#fef3f8',
      background2: '#fce7f3',
      background3: '#f5d0fe',
      cardBg: 'rgba(255, 255, 255, 0.95)',
      border: 'rgba(249, 168, 212, 0.4)',
      text: '#831843',
      textLight: '#9f1239',
      success: '#ec4899',
      error: '#be123c'
    }
  },
  darkFeminine: {
    name: 'Dark Floral Night',
    icon: '🌺',
    description: 'Ciemny kobiecy motyw z mnóstwem kwiatków',
    class: 'dark-feminine',
    colors: {
      primary: '#f472b6',
      primaryDark: '#ec4899',
      primaryLight: '#f9a8d4',
      primaryLighter: '#fbcfe8',
      background1: '#1a0a14',
      background2: '#2d1b2e',
      background3: '#0f0511',
      cardBg: 'rgba(45, 27, 46, 0.9)',
      border: 'rgba(236, 72, 153, 0.3)',
      text: '#fce7f3',
      textLight: '#f9a8d4',
      success: '#f472b6',
      error: '#fb7185'
    }
  }
}

export const avatars = [
  '🦁', '🐯', '🦊', '🐼', '🐨', '🐸',
  '🦄', '🦋', '🐙', '🦀', '🐬', '🦅',
  '🌟', '🔥', '⚡', '🌈', '🎨', '🎭',
  '🎮', '🎸', '🎯', '🚀', '🌙', '☀️'
]

export function applyTheme(themeName) {
  const theme = themes[themeName]
  if (!theme) return

  const root = document.documentElement

  // Remove all theme classes
  Object.values(themes).forEach(t => {
    root.classList.remove(t.class)
  })

  // Add new theme class
  root.classList.add(theme.class)

  // Set CSS variables
  Object.entries(theme.colors).forEach(([key, value]) => {
    root.style.setProperty(`--color-${key}`, value)
  })
}

export function getTextSizeClass(size) {
  const sizes = {
    small: 'text-sm',
    medium: 'text-base',
    large: 'text-lg'
  }
  return sizes[size] || sizes.medium
}
