<template>
  <div class="settings-container">
    <div class="settings-panel">
      <div class="settings-header">
        <button class="back-btn" @click="$emit('close-settings')">
          ← Powrót
        </button>
        <h2>⚙️ Ustawienia</h2>
      </div>

      <div class="settings-content">
        <!-- Theme Selection -->
        <div class="settings-section">
          <h3>🎨 Motyw</h3>
          <div class="theme-grid">
            <div
              v-for="(theme, key) in themes"
              :key="key"
              :class="['theme-card', { active: currentTheme === key }]"
              @click="changeTheme(key)"
            >
              <div class="theme-preview" :style="getThemePreview(theme)"></div>
              <span class="theme-name">{{ theme.name }}</span>
              <span v-if="currentTheme === key" class="theme-check">✓</span>
            </div>
          </div>
        </div>

        <!-- Text Size -->
        <div class="settings-section">
          <h3>📏 Rozmiar tekstu</h3>
          <div class="text-size-options">
            <button
              v-for="size in textSizes"
              :key="size.value"
              :class="['size-btn', { active: currentTextSize === size.value }]"
              @click="changeTextSize(size.value)"
            >
              {{ size.label }}
            </button>
          </div>
          <div class="text-preview" :class="getTextSizeClass(currentTextSize)">
            Przykładowy tekst wiadomości
          </div>
        </div>

        <!-- Avatar Selection -->
        <div class="settings-section">
          <h3>🎭 Avatar</h3>
          <div class="avatar-grid">
            <div
              v-for="avatar in avatars"
              :key="avatar"
              :class="['avatar-option', { active: currentAvatar === avatar }]"
              @click="changeAvatar(avatar)"
            >
              <span class="avatar-emoji">{{ avatar }}</span>
              <span v-if="currentAvatar === avatar" class="avatar-check">✓</span>
            </div>
          </div>
        </div>

        <!-- Avatar Background Color -->
        <div class="settings-section">
          <h3>🎨 Kolor tła avatara</h3>
          <div class="color-grid">
            <div
              v-for="(color, index) in avatarColors"
              :key="index"
              :class="['color-option', { active: currentAvatarColor === index }]"
              :style="{ background: color.gradient }"
              @click="changeAvatarColor(index)"
            >
              <span v-if="currentAvatarColor === index" class="color-check">✓</span>
            </div>
          </div>
        </div>

        <!-- Profile Photo Upload -->
        <div class="settings-section">
          <h3>📷 Zdjęcie profilowe</h3>
          <div class="profile-photo-section">
            <div class="current-photo">
              <img v-if="userProfilePhoto" :src="getFullPhotoUrl(userProfilePhoto)" alt="Profile" class="profile-img" />
              <div v-else class="no-photo">Brak zdjęcia</div>
            </div>
            <div class="photo-controls">
              <input
                type="file"
                ref="photoInput"
                accept="image/*"
                style="display: none"
                @change="handlePhotoUpload"
              />
              <button class="upload-btn" @click="$refs.photoInput.click()">
                Wybierz zdjęcie
              </button>
              <button v-if="userProfilePhoto" class="remove-btn" @click="removeProfilePhoto">
                Usuń zdjęcie
              </button>
            </div>
            <p class="photo-info">Max 5MB, formaty: JPG, PNG, GIF, WEBP</p>
          </div>
        </div>

        <!-- User Info -->
        <div class="settings-section">
          <h3>👤 Informacje o koncie</h3>
          <div class="user-details">
            <p><strong>Nazwa użytkownika:</strong> {{ currentUser?.username }}</p>
            <p><strong>Email:</strong> {{ currentUser?.email }}</p>
          </div>
        </div>

        <!-- Save Settings -->
        <div class="settings-section save-section">
          <button
            class="save-btn"
            @click="saveSettings"
            :disabled="isSaving"
          >
            {{ isSaving ? 'Zapisywanie...' : '💾 Zapisz ustawienia' }}
          </button>
          <p v-if="saveMessage" :class="['save-message', saveMessageType]">
            {{ saveMessage }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { themes, avatars, applyTheme, getTextSizeClass } from '../themes.js'

export default {
  name: 'Settings',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      themes,
      avatars,
      currentTheme: localStorage.getItem('theme') || 'purpleDream',
      currentTextSize: localStorage.getItem('textSize') || 'medium',
      currentAvatar: localStorage.getItem('userAvatar') || '🦁',
      currentAvatarColor: parseInt(localStorage.getItem('avatarBgColor') || '0'),
      textSizes: [
        { value: 'small', label: 'Mały' },
        { value: 'medium', label: 'Średni' },
        { value: 'large', label: 'Duży' }
      ],
      avatarColors: [
        { name: 'Niebieski-Fioletowy', gradient: 'linear-gradient(to bottom right, #3b82f6, #8b5cf6)' },
        { name: 'Różowy-Czerwony', gradient: 'linear-gradient(to bottom right, #ec4899, #ef4444)' },
        { name: 'Zielony-Cyjan', gradient: 'linear-gradient(to bottom right, #10b981, #06b6d4)' },
        { name: 'Pomarańczowy-Żółty', gradient: 'linear-gradient(to bottom right, #f97316, #eab308)' },
        { name: 'Fioletowy-Różowy', gradient: 'linear-gradient(to bottom right, #a855f7, #ec4899)' },
        { name: 'Czerwony-Fioletowy', gradient: 'linear-gradient(to bottom right, #dc2626, #7c3aed)' },
        { name: 'Cyjan-Niebieski', gradient: 'linear-gradient(to bottom right, #06b6d4, #3b82f6)' }
      ],
      isSaving: false,
      saveMessage: '',
      saveMessageType: '',
      userProfilePhoto: this.currentUser?.avatar || null,
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api'
    }
  },
  mounted() {
    applyTheme(this.currentTheme)
    this.applyTextSize(this.currentTextSize)
    this.userProfilePhoto = this.currentUser?.avatar || null
  },
  methods: {
    getTextSizeClass,
    changeTheme(themeName) {
      this.currentTheme = themeName
      localStorage.setItem('theme', themeName)
      applyTheme(themeName)
      this.$emit('theme-changed', themeName)
    },
    changeTextSize(size) {
      this.currentTextSize = size
      localStorage.setItem('textSize', size)
      this.applyTextSize(size)
      this.$emit('text-size-changed', size)
    },
    applyTextSize(size) {
      document.body.classList.remove('text-size-small', 'text-size-medium', 'text-size-large')
      document.body.classList.add(getTextSizeClass(size))
    },
    changeAvatar(avatar) {
      this.currentAvatar = avatar
      localStorage.setItem('userAvatar', avatar)
      this.$emit('avatar-changed', avatar)
    },
    changeAvatarColor(colorIndex) {
      this.currentAvatarColor = colorIndex
      localStorage.setItem('avatarBgColor', colorIndex.toString())
      this.$emit('avatar-color-changed', colorIndex)
    },
    getThemePreview(theme) {
      return {
        background: `linear-gradient(135deg, ${theme.colors.primary} 0%, ${theme.colors.primaryDark} 100%)`
      }
    },
    async saveSettings() {
      this.isSaving = true
      this.saveMessage = ''
      this.saveMessageType = ''

      // Settings are already saved to localStorage when changed
      // This button just confirms the save
      setTimeout(() => {
        this.saveMessage = '✓ Ustawienia zostały zapisane pomyślnie!'
        this.saveMessageType = 'success'
        this.isSaving = false

        // Auto-hide message after 3 seconds
        setTimeout(() => {
          this.saveMessage = ''
        }, 3000)
      }, 500)
    },
    getFullPhotoUrl(photoPath) {
      if (!photoPath) return ''
      if (photoPath.startsWith('http')) return photoPath
      const baseUrl = this.apiUrl.replace('/api', '')
      return baseUrl + photoPath
    },
    async handlePhotoUpload(event) {
      const file = event.target.files[0]
      if (!file) return

      // Validate file size
      if (file.size > 5 * 1024 * 1024) {
        alert('Plik jest za duży (max 5MB)')
        return
      }

      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
      if (!allowedTypes.includes(file.type)) {
        alert('Nieprawidłowy typ pliku. Dozwolone: JPG, PNG, GIF, WEBP')
        return
      }

      try {
        const formData = new FormData()
        formData.append('file', file)

        const token = localStorage.getItem('token')
        const response = await fetch(
          `${this.apiUrl}/auth/users/${this.currentUser.id}/upload-photo`,
          {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`
            },
            body: formData
          }
        )

        if (response.ok) {
          const data = await response.json()
          this.userProfilePhoto = data.avatar

          // Update localStorage
          const user = JSON.parse(localStorage.getItem('user'))
          user.avatar = data.avatar
          localStorage.setItem('user', JSON.stringify(user))

          this.$emit('avatar-updated', data.avatar)
          alert('Zdjęcie profilowe zostało zaktualizowane!')
        } else {
          alert('Nie udało się przesłać zdjęcia')
        }
      } catch (error) {
        console.error('Error uploading photo:', error)
        alert('Błąd podczas przesyłania zdjęcia')
      }
    },
    async removeProfilePhoto() {
      if (!confirm('Czy na pewno chcesz usunąć zdjęcie profilowe?')) return

      try {
        // In a real app, you'd call an API to delete the photo
        // For now, just clear it locally
        this.userProfilePhoto = null

        const user = JSON.parse(localStorage.getItem('user'))
        user.avatar = null
        localStorage.setItem('user', JSON.stringify(user))

        this.$emit('avatar-updated', null)
        alert('Zdjęcie profilowe zostało usunięte')
      } catch (error) {
        console.error('Error removing photo:', error)
        alert('Błąd podczas usuwania zdjęcia')
      }
    }
  }
}
</script>

<style scoped>
.settings-container {
  width: 100%;
  max-width: 1200px;
  height: 90vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.settings-panel {
  width: 100%;
  height: 100%;
  background: var(--color-cardBg, linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%));
  backdrop-filter: blur(10px);
  border: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.settings-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
}

.back-btn {
  padding: 10px 20px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-border, rgba(255, 255, 255, 0.3));
  border-radius: 12px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.back-btn:hover {
  background: rgba(0, 0, 0, 0.5);
  transform: translateX(-3px);
}

.settings-header h2 {
  color: var(--color-primaryLight, #a78bfa);
  font-size: 28px;
  text-shadow: 0 0 20px rgba(167, 139, 250, 0.5);
  margin: 0;
}

.settings-content {
  flex: 1;
  overflow-y: auto;
  padding-right: 10px;
}

.settings-section {
  margin-bottom: 40px;
}

.settings-section h3 {
  color: var(--color-text, #c4b5fd);
  font-size: 20px;
  margin-bottom: 20px;
}

/* Theme Grid */
.theme-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.theme-card {
  position: relative;
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
}

.theme-card:hover {
  border-color: var(--color-primaryLight, #a78bfa);
  transform: translateY(-3px);
  box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
}

.theme-card.active {
  border-color: var(--color-primary, #8b5cf6);
  background: rgba(139, 92, 246, 0.2);
  box-shadow: 0 5px 20px rgba(139, 92, 246, 0.6);
}

.theme-preview {
  width: 100%;
  height: 60px;
  border-radius: 8px;
  margin-bottom: 10px;
}

.theme-name {
  display: block;
  color: var(--color-text, #c4b5fd);
  font-size: 14px;
  font-weight: 600;
}

.theme-check {
  position: absolute;
  top: 10px;
  right: 10px;
  background: var(--color-primary, #8b5cf6);
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
}

/* Text Size Options */
.text-size-options {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.size-btn {
  flex: 1;
  padding: 12px 24px;
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  color: var(--color-text, #c4b5fd);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.size-btn:hover {
  border-color: var(--color-primaryLight, #a78bfa);
  transform: translateY(-2px);
}

.size-btn.active {
  background: linear-gradient(135deg, var(--color-primary, #8b5cf6) 0%, var(--color-primaryDark, #6d28d9) 100%);
  border-color: var(--color-primary, #8b5cf6);
  color: white;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.text-preview {
  padding: 20px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  color: var(--color-text, #c4b5fd);
  text-align: center;
}

/* Avatar Grid */
.avatar-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  gap: 12px;
}

/* Color Grid */
.color-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 12px;
}

.color-option {
  position: relative;
  border: 3px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  height: 60px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.color-option:hover {
  border-color: var(--color-primaryLight, #a78bfa);
  transform: scale(1.05);
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.color-option.active {
  border-color: var(--color-primary, #8b5cf6);
  border-width: 4px;
  box-shadow: 0 4px 20px rgba(139, 92, 246, 0.6);
}

.color-check {
  background: white;
  color: var(--color-primary, #8b5cf6);
  width: 26px;
  height: 26px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.avatar-option {
  position: relative;
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  padding: 15px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
  aspect-ratio: 1;
}

.avatar-option:hover {
  border-color: var(--color-primaryLight, #a78bfa);
  transform: scale(1.1);
  background: rgba(139, 92, 246, 0.2);
}

.avatar-option.active {
  border-color: var(--color-primary, #8b5cf6);
  background: rgba(139, 92, 246, 0.3);
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.6);
}

.avatar-emoji {
  font-size: 32px;
}

.avatar-check {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--color-primary, #8b5cf6);
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

/* User Details */
.user-details {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid var(--color-border, rgba(139, 92, 246, 0.3));
  border-radius: 12px;
  padding: 20px;
}

.user-details p {
  color: var(--color-text, #c4b5fd);
  margin: 10px 0;
  font-size: 15px;
}

.user-details strong {
  color: var(--color-primaryLight, #a78bfa);
}

/* Scrollbar */
.settings-content::-webkit-scrollbar {
  width: 8px;
}

.settings-content::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.settings-content::-webkit-scrollbar-thumb {
  background: var(--color-primary, #8b5cf6);
  border-radius: 4px;
}

.settings-content::-webkit-scrollbar-thumb:hover {
  background: var(--color-primaryLight, #a78bfa);
}

/* Responsive */
@media (max-width: 768px) {
  .settings-panel {
    padding: 20px;
  }

  .settings-header h2 {
    font-size: 24px;
  }

  .theme-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
  }

  .text-size-options {
    flex-direction: column;
  }

  .avatar-grid {
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 10px;
  }

  .avatar-emoji {
    font-size: 28px;
  }
}

@media (max-width: 480px) {
  .settings-panel {
    padding: 15px;
    border-radius: 15px;
  }

  .settings-header h2 {
    font-size: 20px;
  }

  .settings-section h3 {
    font-size: 18px;
  }

  .theme-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .avatar-grid {
    grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
  }

  .avatar-emoji {
    font-size: 24px;
  }
}

/* Save Section */
.save-section {
  text-align: center;
  padding-top: 10px;
  border-top: 2px solid var(--color-border, rgba(139, 92, 246, 0.3));
}

.save-btn {
  width: 100%;
  max-width: 400px;
  padding: 16px 32px;
  background: linear-gradient(135deg, var(--color-primary, #8b5cf6) 0%, var(--color-primaryDark, #6d28d9) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.6);
}

.save-btn:active:not(:disabled) {
  transform: translateY(0);
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.save-message {
  margin-top: 15px;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  animation: fadeIn 0.3s ease-in;
}

.save-message.success {
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.5);
  color: var(--color-success, #86efac);
}

.save-message.error {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: var(--color-error, #fca5a5);
}

/* Profile Photo Section */
.profile-photo-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
  align-items: center;
}

.current-photo {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid var(--color-primary);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.profile-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-photo {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-cardBg);
  color: var(--color-textMuted);
  font-size: 14px;
}

.photo-controls {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}

.upload-btn, .remove-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-btn {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
}

.upload-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
}

.remove-btn {
  background: rgba(239, 68, 68, 0.2);
  border: 2px solid rgba(239, 68, 68, 0.5);
  color: #fca5a5;
}

.remove-btn:hover {
  background: rgba(239, 68, 68, 0.3);
}

.photo-info {
  font-size: 12px;
  color: var(--color-textMuted);
  text-align: center;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
