<template>
  <div class="min-h-screen max-h-screen overflow-hidden transition-colors duration-300" :class="getBgClass()">
    <!-- Mobile Header -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-50 backdrop-blur-lg border-b" :class="getCardClass()">
      <div class="flex items-center justify-between p-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" :style="getAvatarBgStyle()">
            {{ userAvatar }}
          </div>
          <span class="font-semibold" :class="getTextClass()">{{ currentUser?.username }}</span>
        </div>
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg hover:bg-opacity-10 transition-colors" :class="getHoverClass()">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 bottom-0 w-72 backdrop-blur-lg border-r transition-transform duration-300 z-40"
           :class="[getCardClass(), mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">
      <!-- Logo -->
      <div class="p-6 border-b" :class="getBorderClass()">
        <div class="flex items-center gap-3 mb-4 animate-fade-in">
          <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg border-2 border-black" :class="getLogoBgClass()">
            <span class="text-2xl">💬</span>
          </div>
          <h1 class="text-2xl font-bold" :class="getLogoTextClass()">
            PingMe
          </h1>
        </div>

        <!-- Live Clock -->
        <div class="clock-widget p-3 rounded-lg mb-3" :class="getCardClass()">
          <div class="text-center">
            <div class="text-lg font-bold" :class="getTextClass()">{{ currentTime }}</div>
            <div class="text-xs" :class="getTextMutedClass()">{{ currentDate }}</div>
          </div>
        </div>

        <!-- User Info -->
        <div class="flex items-center gap-3 p-3 rounded-lg" :class="getHoverClass()">
          <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold shadow" :style="getAvatarBgStyle()">
            {{ userAvatar }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold truncate" :class="getTextClass()">{{ currentUser?.username }}</p>
            <p class="text-xs truncate" :class="getTextMutedClass()">Online</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="switchTab(tab.id)"
          class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-200 group animate-slide-up"
          :class="activeTab === tab.id ? getActiveNavClass() : getInactiveNavClass()">
          <span class="text-xl">{{ tab.icon }}</span>
          <span class="flex-1 text-left">{{ tab.label }}</span>
          <span v-if="tab.badge" class="px-2 py-1 text-xs rounded-full bg-red-500 text-white">{{ tab.badge }}</span>
        </button>
      </nav>

      <!-- Logout Button -->
      <div class="p-4 border-t" :class="getBorderClass()">
        <button
          @click="handleLogout"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-medium transition-all duration-200 hover:scale-105"
          :class="getLogoutButtonClass()">
          <span>🚪</span>
          <span>Wyloguj się</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-72 h-screen overflow-y-auto pt-20 lg:pt-0">
      <div class="p-6">
        <!-- Home Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'home'" class="home-panel">
            <div class="welcome-card">
              <h1 class="welcome-title">👋 Witaj, {{ currentUser?.username }}!</h1>
              <p class="welcome-subtitle">Co chcesz dzisiaj zrobić?</p>
            </div>

            <div class="quick-actions">
              <div class="action-card" @click="switchTab('friends')">
                <div class="action-icon">👥</div>
                <h3>Znajomi</h3>
                <p>Czatuj ze znajomymi</p>
              </div>
              <div class="action-card" @click="switchTab('events')">
                <div class="action-icon">📅</div>
                <h3>Wydarzenia</h3>
                <p>Planuj spotkania</p>
              </div>
              <div class="action-card" @click="switchTab('gallery')">
                <div class="action-icon">🖼️</div>
                <h3>Galeria</h3>
                <p>Dziel się zdjęciami</p>
              </div>
              <div class="action-card" @click="switchTab('settings')">
                <div class="action-icon">⚙️</div>
                <h3>Ustawienia</h3>
                <p>Personalizuj aplikację</p>
              </div>
            </div>
          </div>
        </transition>

        <!-- Info Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'info'" class="info-panel">
            <h1 class="info-title">ℹ️ Informacje o PingMe</h1>

            <div class="info-section">
              <h2>📋 Funkcjonalności</h2>
              <ul class="info-list">
                <li>💬 Chat w czasie rzeczywistym z przesyłaniem plików</li>
                <li>👥 Zarządzanie znajomymi i niestandardowe pseudonimy</li>
                <li>🖼️ Galeria zdjęć i wideo (JPG, PNG, MP4, GIF)</li>
                <li>📅 Kalendarz wydarzeń i lista zadań To-Do</li>
                <li>😄 GIF picker z integracją Giphy</li>
                <li>❤️ Reakcje emoji na wiadomości</li>
                <li>✏️ Edycja i usuwanie wiadomości</li>
                <li>🎨 4 motywy kolorystyczne (Dark Modern, Light Modern, Feminine Garden, Dark Floral Night)</li>
                <li>🎭 Personalizacja avatara (24 emoji + 7 kolorów tła)</li>
                <li>📱 Responsive design (mobile & desktop)</li>
              </ul>
            </div>

            <div class="info-section">
              <h2>🛠️ Technologie</h2>
              <div class="tech-columns">
                <div>
                  <h3>Frontend:</h3>
                  <ul class="tech-list">
                    <li>Vue.js 3</li>
                    <li>Tailwind CSS</li>
                    <li>Vue Router</li>
                    <li>Giphy API</li>
                  </ul>
                </div>
                <div>
                  <h3>Backend:</h3>
                  <ul class="tech-list">
                    <li>Symfony 6</li>
                    <li>Doctrine ORM</li>
                    <li>MySQL/SQLite</li>
                    <li>REST API</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="info-section">
              <h2>📊 Statystyki projektu</h2>
              <div class="stats-grid">
                <div class="stat-card">
                  <div class="stat-value">6</div>
                  <div class="stat-label">Głównych sekcji</div>
                </div>
                <div class="stat-card">
                  <div class="stat-value">4</div>
                  <div class="stat-label">Motywy</div>
                </div>
                <div class="stat-card">
                  <div class="stat-value">10+</div>
                  <div class="stat-label">Funkcjonalności</div>
                </div>
              </div>
            </div>

            <div class="info-footer">
              <p>© 2025 PingMe - Platforma komunikacji</p>
              <p>Kontakt: <a href="mailto:kontakt@pingme.pl">kontakt@pingme.pl</a></p>
            </div>
          </div>
        </transition>

        <!-- Friends Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'friends' && !selectedFriend">
            <Friends :currentUser="currentUser" @open-chat="openChat" />
          </div>
        </transition>

        <!-- Events Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'events'">
            <Events :currentUser="currentUser" />
          </div>
        </transition>

        <!-- Gallery Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'gallery'">
            <Gallery :currentUser="currentUser" />
          </div>
        </transition>

        <!-- Settings Tab -->
        <transition
          enter-active-class="animate-fade-in"
          leave-active-class="animate-fade-out">
          <div v-if="activeTab === 'settings'">
            <Settings
              :currentUser="currentUser"
              @close-settings="closeSettings"
              @avatar-changed="updateAvatar"
              @avatar-color-changed="handleAvatarColorChange"
              @theme-changed="handleThemeChange"
              @text-size-changed="handleTextSizeChange"
            />
          </div>
        </transition>

        <!-- Chat Room -->
        <transition
          enter-active-class="animate-slide-up"
          leave-active-class="animate-fade-out">
          <div v-if="selectedFriend">
            <ChatRoom
              :currentUser="currentUser"
              :friend="selectedFriend"
              @close-chat="closeChat"
            />
          </div>
        </transition>
      </div>
    </main>

    <!-- Mobile Menu Overlay -->
    <div v-if="mobileMenuOpen"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"></div>

    <!-- Footer -->
    <footer class="fixed bottom-0 left-0 right-0 lg:left-72 p-4 backdrop-blur-lg border-t z-20" :class="getCardClass()">
      <div class="flex items-center justify-between max-w-screen-xl mx-auto">
        <a href="mailto:kontakt@pingme.pl" class="flex items-center gap-2 text-sm hover:text-blue-500 transition-colors" :class="getTextMutedClass()">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          kontakt@pingme.pl
        </a>
        <div class="flex items-center gap-4">
          <p class="text-xs" :class="getTextMutedClass()">© 2025 PingMe. Wszelkie prawa zastrzeżone.</p>
          <p class="text-xs font-mono" :class="getTextMutedClass()">IP: {{ userIp }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import Friends from './Friends.vue'
import Events from './Events.vue'
import Gallery from './Gallery.vue'
import Settings from './Settings.vue'
import ChatRoom from './ChatRoom.vue'
import { applyTheme, getTextSizeClass } from '../themes.js'

export default {
  name: 'Dashboard',
  components: {
    Friends,
    Events,
    Gallery,
    Settings,
    ChatRoom
  },
  data() {
    return {
      currentUser: null,
      activeTab: 'home',
      selectedFriend: null,
      userAvatar: '🦁',
      currentTheme: 'darkModern',
      currentAvatarColor: 0,
      mobileMenuOpen: false,
      currentTime: '',
      currentDate: '',
      userIp: '',
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
      tabs: [
        { id: 'home', icon: '🏠', label: 'Strona główna', badge: null },
        { id: 'friends', icon: '👥', label: 'Znajomi', badge: null },
        { id: 'events', icon: '📅', label: 'Wydarzenia', badge: null },
        { id: 'gallery', icon: '🖼️', label: 'Galeria', badge: null },
        { id: 'settings', icon: '⚙️', label: 'Ustawienia', badge: null },
        { id: 'info', icon: 'ℹ️', label: 'Informacje', badge: null }
      ],
      avatarColors: [
        'linear-gradient(to bottom right, #3b82f6, #8b5cf6)',
        'linear-gradient(to bottom right, #ec4899, #ef4444)',
        'linear-gradient(to bottom right, #10b981, #06b6d4)',
        'linear-gradient(to bottom right, #f97316, #eab308)',
        'linear-gradient(to bottom right, #a855f7, #ec4899)',
        'linear-gradient(to bottom right, #dc2626, #7c3aed)',
        'linear-gradient(to bottom right, #06b6d4, #3b82f6)'
      ]
    }
  },
  async mounted() {
    const storedUser = localStorage.getItem('user')
    const storedToken = localStorage.getItem('token')

    // Require both user and JWT token
    if (!storedUser || !storedToken) {
      // Clear ALL localStorage data
      localStorage.clear()
      // Redirect immediately without alert to avoid bypassing
      this.$router.push('/').catch(() => {
        // Force reload if router fails
        window.location.href = '/'
      })
      return
    }

    // Check IP and session validity
    await this.checkIpAndSession()

    this.currentUser = JSON.parse(storedUser)
    const storedAvatar = localStorage.getItem('userAvatar')
    if (storedAvatar) {
      this.userAvatar = storedAvatar
    }

    const storedAvatarColor = localStorage.getItem('avatarBgColor')
    if (storedAvatarColor) {
      this.currentAvatarColor = parseInt(storedAvatarColor)
    }

    const storedTheme = localStorage.getItem('theme') || 'darkModern'
    this.currentTheme = storedTheme
    applyTheme(storedTheme)

    const storedTextSize = localStorage.getItem('textSize') || 'medium'
    document.body.classList.add(getTextSizeClass(storedTextSize))

    // Initialize and start clock
    this.updateClock()
    this.clockInterval = setInterval(this.updateClock, 1000)

    // Check inactivity every minute
    this.inactivityInterval = setInterval(this.checkInactivity, 60000)

    // Track user activity
    this.setupActivityTracking()
  },
  beforeUnmount() {
    if (this.clockInterval) {
      clearInterval(this.clockInterval)
    }
    if (this.inactivityInterval) {
      clearInterval(this.inactivityInterval)
    }
    // Remove activity listeners
    document.removeEventListener('mousemove', this.updateActivity)
    document.removeEventListener('keypress', this.updateActivity)
    document.removeEventListener('click', this.updateActivity)
  },
  methods: {
    updateClock() {
      const now = new Date()
      this.currentTime = now.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
      this.currentDate = now.toLocaleDateString('pl-PL', { weekday: 'short', year: 'numeric', month: 'long', day: 'numeric' })
    },
    switchTab(tab) {
      this.activeTab = tab
      this.selectedFriend = null
      this.mobileMenuOpen = false
    },
    openChat(friend) {
      this.selectedFriend = friend
      this.activeTab = 'friends'
      this.mobileMenuOpen = false
    },
    closeChat() {
      this.selectedFriend = null
    },
    closeSettings() {
      this.activeTab = 'friends'
    },
    handleLogout() {
      localStorage.removeItem('user')
      localStorage.removeItem('token')
      this.$router.push('/')
    },
    updateAvatar(avatar) {
      this.userAvatar = avatar
      localStorage.setItem('userAvatar', avatar)
    },
    handleAvatarColorChange(colorIndex) {
      this.currentAvatarColor = colorIndex
      localStorage.setItem('avatarBgColor', colorIndex.toString())
    },
    handleThemeChange(theme) {
      this.currentTheme = theme
      applyTheme(theme)
    },
    handleTextSizeChange(size) {
      document.body.classList.remove('text-sm', 'text-base', 'text-lg')
      document.body.classList.add(getTextSizeClass(size))
    },
    getAvatarBgStyle() {
      return {
        background: this.avatarColors[this.currentAvatarColor]
      }
    },
    getBgClass() {
      if (this.currentTheme === 'feminine') return 'bg-pink-50'
      if (this.currentTheme === 'darkFeminine') return 'bg-gradient-to-br from-purple-950 via-pink-950 to-purple-900'
      if (this.currentTheme === 'lightModern') return 'bg-gray-50'
      return 'bg-slate-900'
    },
    getCardClass() {
      if (this.currentTheme === 'feminine') return 'bg-white/90 border-pink-200'
      if (this.currentTheme === 'darkFeminine') return 'bg-purple-950/90 border-pink-500/30'
      if (this.currentTheme === 'lightModern') return 'bg-white/90 border-gray-200'
      return 'bg-slate-800/90 border-slate-700'
    },
    getBorderClass() {
      if (this.currentTheme === 'feminine') return 'border-pink-200'
      if (this.currentTheme === 'darkFeminine') return 'border-pink-500/30'
      if (this.currentTheme === 'lightModern') return 'border-gray-200'
      return 'border-slate-700'
    },
    getTextClass() {
      if (this.currentTheme === 'feminine') return 'text-pink-900'
      if (this.currentTheme === 'darkFeminine') return 'text-pink-100'
      if (this.currentTheme === 'lightModern') return 'text-gray-900'
      return 'text-slate-100'
    },
    getTextMutedClass() {
      if (this.currentTheme === 'feminine') return 'text-pink-600'
      if (this.currentTheme === 'darkFeminine') return 'text-pink-300'
      if (this.currentTheme === 'lightModern') return 'text-gray-600'
      return 'text-slate-400'
    },
    getHoverClass() {
      if (this.currentTheme === 'feminine') return 'hover:bg-pink-100'
      if (this.currentTheme === 'darkFeminine') return 'hover:bg-pink-900/30'
      if (this.currentTheme === 'lightModern') return 'hover:bg-gray-100'
      return 'hover:bg-slate-700'
    },
    getActiveNavClass() {
      if (this.currentTheme === 'feminine') return 'bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-lg shadow-pink-500/50'
      if (this.currentTheme === 'darkFeminine') return 'bg-gradient-to-r from-pink-600 to-purple-600 text-white shadow-lg shadow-pink-500/50'
      if (this.currentTheme === 'lightModern') return 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/50'
      return 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg shadow-blue-500/50'
    },
    getInactiveNavClass() {
      if (this.currentTheme === 'feminine') return 'text-pink-700 hover:bg-pink-100'
      if (this.currentTheme === 'darkFeminine') return 'text-pink-200 hover:bg-pink-900/30'
      if (this.currentTheme === 'lightModern') return 'text-gray-700 hover:bg-gray-100'
      return 'text-slate-300 hover:bg-slate-700'
    },
    getLogoutButtonClass() {
      if (this.currentTheme === 'feminine') return 'bg-pink-100 text-pink-700 hover:bg-pink-200'
      if (this.currentTheme === 'darkFeminine') return 'bg-pink-900/50 text-pink-200 hover:bg-pink-800/50'
      if (this.currentTheme === 'lightModern') return 'bg-gray-100 text-gray-700 hover:bg-gray-200'
      return 'bg-slate-700 text-slate-300 hover:bg-slate-600'
    },
    getLogoBgClass() {
      if (this.currentTheme === 'feminine') return 'bg-pink-50'
      if (this.currentTheme === 'darkFeminine') return 'bg-gradient-to-br from-purple-900 to-pink-900'
      if (this.currentTheme === 'lightModern') return 'bg-gray-50'
      return 'bg-slate-800'
    },
    getLogoTextClass() {
      if (this.currentTheme === 'feminine') return 'text-pink-700'
      if (this.currentTheme === 'darkFeminine') return 'text-pink-200'
      if (this.currentTheme === 'lightModern') return 'text-gray-800'
      return 'text-slate-100'
    },
    async checkIpAndSession() {
      try {
        // Get current IP for display only (JWT handles authentication)
        const response = await fetch(`${this.apiUrl}/auth/ip`)
        const data = await response.json()
        this.userIp = data.ip

        // Just display IP, don't enforce logout on change (JWT handles security)
        localStorage.setItem('userIp', data.ip)
      } catch (error) {
        console.error('Error checking IP:', error)
      }
    },
    checkInactivity() {
      const lastActivity = localStorage.getItem('lastActivity')
      if (!lastActivity) return

      const now = new Date()
      const lastActivityTime = new Date(lastActivity)
      const minutesInactive = (now - lastActivityTime) / (1000 * 60)

      if (minutesInactive >= 15) {
        alert('Sesja wygasła z powodu braku aktywności. Zaloguj się ponownie.')
        this.handleLogout()
      }
    },
    setupActivityTracking() {
      this.updateActivity = () => {
        localStorage.setItem('lastActivity', new Date().toISOString())
      }

      document.addEventListener('mousemove', this.updateActivity)
      document.addEventListener('keypress', this.updateActivity)
      document.addEventListener('click', this.updateActivity)
    }
  }
}
</script>

<style scoped>
/* Home Panel */
.home-panel {
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-card {
  text-align: center;
  padding: 60px 20px;
  margin-bottom: 40px;
}

.welcome-title {
  font-size: 42px;
  font-weight: bold;
  margin-bottom: 15px;
}

.welcome-subtitle {
  font-size: 20px;
  opacity: 0.8;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  padding: 20px;
}

.action-card {
  padding: 40px 30px;
  border-radius: 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.action-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.action-card h3 {
  font-size: 24px;
  margin-bottom: 10px;
  font-weight: bold;
}

.action-card p {
  opacity: 0.7;
  font-size: 16px;
}

.action-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

/* Info Panel */
.info-panel {
  max-width: 1000px;
  margin: 0 auto;
}

.info-title {
  font-size: 36px;
  font-weight: bold;
  margin-bottom: 40px;
  text-align: center;
}

.info-section {
  padding: 30px;
  border-radius: 16px;
  margin-bottom: 30px;
}

.info-section h2 {
  font-size: 28px;
  margin-bottom: 20px;
  font-weight: bold;
}

.info-section h3 {
  font-size: 20px;
  margin-bottom: 12px;
  font-weight: bold;
}

.info-list {
  list-style: none;
  padding: 0;
}

.info-list li {
  padding: 12px 0;
  font-size: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-list li:last-child {
  border-bottom: none;
}

.tech-columns {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
}

.tech-list {
  list-style: none;
  padding: 0;
}

.tech-list li {
  padding: 8px 0;
  padding-left: 20px;
  position: relative;
  font-size: 16px;
}

.tech-list li::before {
  content: '▸';
  position: absolute;
  left: 0;
  font-weight: bold;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.stat-card {
  text-align: center;
  padding: 30px;
  border-radius: 16px;
}

.stat-value {
  font-size: 48px;
  font-weight: bold;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 16px;
  opacity: 0.7;
}

.info-footer {
  text-align: center;
  padding: 30px;
  margin-top: 40px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  opacity: 0.7;
}

.info-footer a {
  text-decoration: underline;
}

/* Theme-specific colors */
.home-panel .action-card,
.info-panel .info-section,
.info-panel .stat-card {
  background: var(--color-cardBg);
  border-color: var(--color-border);
}

.home-panel .action-card:hover {
  border-color: var(--color-primaryLight);
}

.home-panel .welcome-title,
.home-panel .action-card h3,
.info-panel .info-title,
.info-panel h2 {
  color: var(--color-primaryLight);
}

.home-panel .welcome-subtitle,
.home-panel .action-card p,
.info-panel .info-list li,
.info-panel .tech-list li {
  color: var(--color-text);
}

.info-panel .tech-list li::before {
  color: var(--color-primary);
}

.info-panel .stat-value {
  color: var(--color-primary);
}

.info-panel .stat-label {
  color: var(--color-textLight);
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-title {
    font-size: 32px;
  }

  .action-icon {
    font-size: 48px;
  }

  .action-card h3 {
    font-size: 20px;
  }

  .info-title {
    font-size: 28px;
  }

  .info-section h2 {
    font-size: 24px;
  }
}
</style>
