<template>
  <div class="chatroom-container">
    <div class="chatroom-header">
      <button class="back-btn" @click="$emit('close-chat')">
        ← Powrót
      </button>
      <div class="chat-user-info">
        <div class="chat-avatar">{{ friend.username.charAt(0).toUpperCase() }}</div>
        <div class="chat-username">
          <span v-if="friendNickname">
            {{ friendNickname }} <span class="real-username">({{ friend.username }})</span>
          </span>
          <span v-else>{{ friend.username }}</span>
        </div>
      </div>
      <div class="header-spacer"></div>
      <button class="personalize-btn" @click="showPersonalizeModal = true" title="Personalizuj czat">
        🎨
      </button>
    </div>

    <!-- Personalization Modal -->
    <div v-if="showPersonalizeModal" class="modal-overlay" @click="closePersonalizeModal">
      <div class="personalize-modal" @click.stop>
        <div class="modal-header">
          <h3>🎨 Personalizacja czatu</h3>
          <button class="modal-close-btn" @click="closePersonalizeModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="color-section">
            <label>Kolor moich wiadomości:</label>
            <div class="color-picker-row">
              <input type="color" v-model="chatCustomization.myMessageColor" />
              <span class="color-preview" :style="{ background: chatCustomization.myMessageColor }"></span>
              <button class="reset-btn" @click="resetColor('myMessageColor')">Resetuj</button>
            </div>
          </div>
          <div class="color-section">
            <label>Kolor wiadomości znajomego:</label>
            <div class="color-picker-row">
              <input type="color" v-model="chatCustomization.friendMessageColor" />
              <span class="color-preview" :style="{ background: chatCustomization.friendMessageColor }"></span>
              <button class="reset-btn" @click="resetColor('friendMessageColor')">Resetuj</button>
            </div>
          </div>
          <div class="color-section">
            <label>Kolor tła czatu:</label>
            <div class="color-picker-row">
              <input type="color" v-model="chatCustomization.backgroundColor" />
              <span class="color-preview" :style="{ background: chatCustomization.backgroundColor }"></span>
              <button class="reset-btn" @click="resetColor('backgroundColor')">Resetuj</button>
            </div>
          </div>
          <div class="color-section">
            <label>Rozmiar tekstu: {{ chatCustomization.fontSize }}px</label>
            <div class="font-size-row">
              <input
                type="range"
                v-model.number="chatCustomization.fontSize"
                min="10"
                max="20"
                step="1"
                class="font-size-slider"
              />
              <button class="reset-btn" @click="resetFontSize">Resetuj</button>
            </div>
          </div>
          <div class="modal-actions">
            <button class="save-btn" @click="savePersonalization">Zapisz</button>
            <button class="cancel-btn" @click="closePersonalizeModal">Anuluj</button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="chat-area"
      ref="chatArea"
      @drop="handleDrop"
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      :class="{ 'drag-over': isDragging }"
      :style="getChatAreaStyle()"
    >
      <div v-if="isDragging" class="drop-overlay">
        📎 Upuść plik tutaj
      </div>

      <div v-if="messages.length === 0" class="empty-chat-message">
        Brak wiadomości. Zacznij konwersację!
      </div>

      <!-- Group messages by date -->
      <div v-for="group in groupedMessages" :key="group.date">
        <!-- Date separator -->
        <div class="date-separator">
          <span class="date-label">{{ group.label }}</span>
        </div>

        <!-- Messages in this date group -->
        <div
          v-for="message in group.messages"
          :key="message.id"
          :class="['message', message.author?.id === currentUser.id ? 'sent' : 'received']"
        >
        <div class="message-wrapper">
          <div class="message-bubble" :style="getMessageBubbleStyle(message.author?.id === currentUser.id)">
            <!-- Display attachment if present -->
            <div v-if="message.attachmentType === 'image'" class="message-attachment">
              <img :src="message.attachmentUrl" :alt="message.attachmentName" class="attachment-image" @click="openImageModal(message.attachmentUrl)" />
            </div>
            <div v-else-if="message.attachmentType === 'video'" class="message-attachment">
              <video :src="message.attachmentUrl" controls class="attachment-video"></video>
            </div>
            <div v-else-if="message.attachmentType === 'gif'" class="message-attachment">
              <img :src="message.attachmentUrl" alt="GIF" class="attachment-gif" />
            </div>

            <!-- Edit mode -->
            <div v-if="editingMessageId === message.id" class="edit-message-form">
              <input
                v-model="editMessageText"
                type="text"
                @keyup.enter="saveEdit(message.id)"
                @keyup.esc="cancelEdit"
                class="edit-input"
                :ref="'editInput' + message.id"
              />
              <button @click="saveEdit(message.id)" class="save-edit-btn">✓</button>
              <button @click="cancelEdit" class="cancel-edit-btn">✕</button>
            </div>
            <!-- Normal display -->
            <div v-else class="message-content">
              <span v-if="message.content && !message.deleted">
                {{ message.content }}
                <span v-if="message.edited" class="edited-label">(edytowano)</span>
              </span>
              <span v-if="message.deleted" class="deleted-message">USUNIĘTO</span>
            </div>
          </div>

          <!-- Message controls (shown on hover) -->
          <div class="message-controls">
            <!-- Reaction toggle button -->
            <button
              class="reaction-toggle-btn"
              @click="toggleReactionMenu(message.id, $event)"
              title="Dodaj reakcję"
            >
              +
            </button>

            <!-- Message actions for own messages -->
            <div v-if="message.author?.id === currentUser.id && !message.deleted" class="message-actions">
              <button
                class="action-btn edit-btn"
                @click="startEdit(message)"
                title="Edytuj"
              >
                ✏️
              </button>
              <button
                class="action-btn delete-btn"
                @click="deleteMessage(message.id)"
                title="Usuń"
              >
                🗑️
              </button>
            </div>
          </div>
        </div>
        <div class="message-time">
          {{ formatTime(message.createdAt) }}
        </div>

        <!-- Wyświetl aktywne reakcje (te z liczbą > 0) -->
        <div v-if="hasActiveReactions(message)" class="active-reactions">
          <span
            v-for="(emoji, type) in reactionEmojis"
            :key="type"
            v-show="message.reactions && message.reactions[type] > 0"
            class="reaction-badge"
            @click="toggleReaction(message.id, type)"
          >
            {{ emoji }}
          </span>
        </div>

        <!-- Menu rozwijane z wszystkimi reakcjami -->
        <div v-if="openReactionMenuId === message.id" class="reactions-menu">
          <button
            v-for="(emoji, type) in reactionEmojis"
            :key="type"
            class="reaction-btn"
            :class="{ active: message.reactions && message.reactions[type] > 0 }"
            @click="addReaction(message.id, type)"
          >
            {{ emoji }}
          </button>
        </div>
        </div>
      </div>
    </div>

    <!-- Typing indicator -->
    <div v-if="isTyping" class="typing-indicator">
      <span class="typing-text">{{ typingUsername }} pisze</span>
      <span class="typing-dots">
        <span class="dot"></span>
        <span class="dot"></span>
        <span class="dot"></span>
      </span>
    </div>

    <div class="input-area">
      <button class="attachment-btn" @click="toggleAttachmentMenu" title="Dodaj załącznik">
        📎
      </button>

      <!-- Attachment Menu -->
      <div v-if="showAttachmentMenu" class="attachment-menu">
        <button @click="openFileDialog" class="menu-item">
          🖼️ Obraz/Wideo
        </button>
        <button @click="toggleGifPicker" class="menu-item">
          😄 GIF
        </button>
      </div>

      <!-- File input (hidden) -->
      <input
        ref="fileInput"
        type="file"
        accept="image/*,video/*"
        @change="handleFileSelect"
        style="display: none"
      />

      <!-- GIF Picker -->
      <div v-if="showGifPicker" class="gif-picker">
        <div class="gif-picker-header">
          <input
            v-model="gifSearchQuery"
            type="text"
            placeholder="Szukaj GIFów..."
            @input="searchGifs"
            class="gif-search-input"
          />
          <button @click="showGifPicker = false" class="close-btn">×</button>
        </div>
        <div v-if="loadingGifs" class="loading-gifs">Ładowanie...</div>
        <div v-else class="gif-grid">
          <img
            v-for="gif in displayedGifs"
            :key="gif.id"
            :src="gif.url"
            :alt="gif.title"
            class="gif-item"
            @click="sendGif(gif.url)"
          />
        </div>
        <div v-if="displayedGifs.length === 0 && !loadingGifs" class="no-gifs">
          Brak GIFów. Wpisz coś w wyszukiwarkę!
        </div>
      </div>

      <!-- Upload Progress -->
      <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress">
        Przesyłanie: {{ uploadProgress }}%
      </div>

      <!-- Preview for selected file -->
      <div v-if="selectedFile" class="file-preview">
        <img v-if="selectedFileType === 'image'" :src="selectedFilePreview" alt="Preview" class="preview-image" />
        <video v-else-if="selectedFileType === 'video'" :src="selectedFilePreview" class="preview-video"></video>
        <button @click="clearSelectedFile" class="clear-btn">×</button>
      </div>

      <input
        v-model="newMessage"
        type="text"
        placeholder="Napisz wiadomość..."
        @keyup.enter="sendMessage"
        ref="messageInput"
      />
      <button @click="sendMessage" :disabled="isSending">
        {{ isSending ? '⏳' : 'Wyślij' }}
      </button>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModal" class="image-modal" @click="closeImageModal">
      <div class="modal-content" @click.stop>
        <img :src="modalImageUrl" alt="Full size image" />
        <button class="modal-close" @click="closeImageModal">×</button>
      </div>
    </div>
  </div>
</template>

<script>
import { generateConversationKey, encryptMessageForConversation, decryptMessageForConversation } from '@/utils/encryption'

export default {
  name: 'ChatRoom',
  props: {
    currentUser: {
      type: Object,
      required: true
    },
    friend: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      messages: [],
      newMessage: '',
      conversationId: null,
      openReactionMenuId: null,
      reactionEmojis: {
        heart: '❤️',
        like: '👍',
        laugh: '😂',
        wow: '😮'
      },
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
      showAttachmentMenu: false,
      showGifPicker: false,
      selectedFile: null,
      selectedFileType: null,
      selectedFilePreview: null,
      uploadProgress: 0,
      isSending: false,
      isDragging: false,
      showImageModal: false,
      modalImageUrl: '',
      gifSearchQuery: '',
      loadingGifs: false,
      trendingGifs: [],
      searchedGifs: [],
      // Giphy API key (public beta key - można użyć własnego)
      giphyApiKey: 'sXpGFDGZs0Dv1mmNFvYaGUvYwKX0PWIh',
      searchTimeout: null,
      editingMessageId: null,
      editMessageText: '',
      isInitialLoad: true,
      showPersonalizeModal: false,
      chatCustomization: {
        myMessageColor: '#3b82f6',
        friendMessageColor: '#6b7280',
        backgroundColor: 'transparent',
        fontSize: 14
      },
      defaultColors: {
        myMessageColor: '#3b82f6',
        friendMessageColor: '#6b7280',
        backgroundColor: 'transparent',
        fontSize: 14
      },
      isTyping: false,
      typingUsername: '',
      typingTimeout: null,
      typingCheckInterval: null,
      conversationKey: null
    }
  },
  computed: {
    displayedGifs() {
      return this.gifSearchQuery ? this.searchedGifs : this.trendingGifs
    },
    friendNickname() {
      const nicknames = JSON.parse(localStorage.getItem('friendNicknames') || '{}')
      return nicknames[this.friend.id] || null
    },
    groupedMessages() {
      const groups = []
      let currentGroup = null

      this.messages.forEach(message => {
        const messageDate = this.getMessageDate(message.createdAt)

        if (!currentGroup || currentGroup.date !== messageDate) {
          currentGroup = {
            date: messageDate,
            label: this.getDateLabel(messageDate),
            messages: []
          }
          groups.push(currentGroup)
        }

        currentGroup.messages.push(message)
      })

      return groups
    }
  },
  watch: {
    newMessage(newVal) {
      if (newVal && newVal.trim() !== '') {
        this.sendTypingNotification()
      }
    }
  },
  mounted() {
    this.initializeConversation()
    // Load trending GIFs on mount
    this.loadTrendingGifs()
    // Auto-refresh messages every 2 seconds for real-time updates
    this.messageInterval = setInterval(() => {
      this.loadMessages()
    }, 2000)
    // Check typing status every 500ms for faster response
    this.typingCheckInterval = setInterval(() => {
      this.checkTypingStatus()
    }, 500)
    // Close reactions menu when clicking outside
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    if (this.messageInterval) {
      clearInterval(this.messageInterval)
    }
    if (this.typingCheckInterval) {
      clearInterval(this.typingCheckInterval)
    }
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout)
    }
    // Mark conversation as read when leaving
    if (this.conversationId) {
      this.markConversationAsRead()
    }
    // Remove click outside listener
    document.removeEventListener('click', this.handleClickOutside)
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      if (!token) {
        console.error('[ChatRoom] No token found - forcing logout')
        localStorage.clear()
        this.$router.push('/login')
        return {}
      }
      return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    },
    async initializeConversation() {
      try {
        // Reset initial load flag when switching conversations
        this.isInitialLoad = true

        const url = `${this.apiUrl}/conversations/${this.currentUser.id}/with/${this.friend.id}`
        const response = await fetch(url, {
          headers: this.getAuthHeaders()
        })
        const data = await response.json()

        this.conversationId = data.id

        // Generate encryption key for this conversation
        this.conversationKey = generateConversationKey(
          this.conversationId,
          this.currentUser.id,
          this.friend.id
        )

        // Load chat customization for this conversation
        this.loadChatCustomization()

        await this.loadMessages()
        await this.markConversationAsRead()
      } catch (error) {
        console.error('Error initializing conversation:', error)
      }
    },
    async loadMessages() {
      if (!this.conversationId) return

      try {
        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages`,
          {
            headers: this.getAuthHeaders()
          }
        )
        const data = await response.json()

        // Fix relative URLs for attachments and decrypt messages
        const messages = Array.isArray(data) ? data : []
        messages.forEach(message => {
          if (message.attachmentUrl && message.attachmentUrl.startsWith('/uploads/')) {
            // Convert relative URL to absolute
            const baseUrl = this.apiUrl.replace('/api', '')
            message.attachmentUrl = baseUrl + message.attachmentUrl
          }

          // Decrypt message content if encrypted
          if (message.content && this.conversationKey) {
            message.content = decryptMessageForConversation(message.content, this.conversationKey)
          }
        })

        this.messages = messages

        this.$nextTick(() => {
          // Only auto-scroll if user is at bottom or it's the initial load
          if (this.isInitialLoad || this.isUserAtBottom()) {
            this.scrollToBottom()
            this.isInitialLoad = false
          }
        })
      } catch (error) {
        console.error('Error loading messages:', error)
      }
    },
    async sendMessage() {
      if ((!this.newMessage.trim() && !this.selectedFile) || !this.conversationId) return

      this.isSending = true

      try {
        let attachmentData = null

        // Upload file first if selected
        if (this.selectedFile) {
          attachmentData = await this.uploadFile(this.selectedFile)
        }

        // Encrypt message content before sending
        const encryptedContent = this.conversationKey && this.newMessage
          ? encryptMessageForConversation(this.newMessage, this.conversationKey)
          : this.newMessage

        // Send message with attachment info
        const messageData = {
          content: encryptedContent,
          authorId: this.currentUser.id
        }

        if (attachmentData) {
          messageData.attachmentType = attachmentData.attachmentType
          messageData.attachmentUrl = attachmentData.attachmentUrl
          messageData.attachmentName = attachmentData.attachmentName
        }

        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages`,
          {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify(messageData)
          }
        )

        if (response.ok) {
          this.newMessage = ''
          this.clearSelectedFile()
          // Clear typing status immediately after sending
          await this.clearTypingStatus()
          await this.loadMessages()
          // Always scroll to bottom after sending a message
          this.$nextTick(() => {
            this.scrollToBottom()
          })
        }
      } catch (error) {
        console.error('Error sending message:', error)
        alert('Błąd podczas wysyłania wiadomości')
      } finally {
        this.isSending = false
      }
    },
    async uploadFile(file) {
      const formData = new FormData()
      formData.append('file', file)

      try {
        // Get JWT token for authorization
        // WAŻNE: NIE ustawiaj Content-Type dla FormData - przeglądarka ustawi automatycznie z boundary
        const token = localStorage.getItem('token')
        if (!token) {
          throw new Error('Brak tokenu autoryzacji')
        }

        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages/upload`,
          {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${token}`
              // NIE dodawaj 'Content-Type' - FormData sam ustawi multipart/form-data
            },
            body: formData
          }
        )

        if (response.ok) {
          const data = await response.json()
          return data
        } else {
          const error = await response.json()
          console.error('Upload failed:', error)
          throw new Error(error.error || 'Upload failed')
        }
      } catch (error) {
        console.error('Error uploading file:', error)
        alert('Błąd podczas przesyłania pliku: ' + error.message)
        throw error
      }
    },
    toggleAttachmentMenu() {
      this.showAttachmentMenu = !this.showAttachmentMenu
      if (this.showAttachmentMenu) {
        this.showGifPicker = false
      }
    },
    openFileDialog() {
      this.$refs.fileInput.click()
      this.showAttachmentMenu = false
    },
    handleFileSelect(event) {
      const file = event.target.files[0]
      if (!file) return

      // Validate file size (max 50MB)
      if (file.size > 50 * 1024 * 1024) {
        alert('Plik jest za duży (max 50MB)')
        return
      }

      this.selectedFile = file
      this.selectedFileType = file.type.startsWith('image/') ? 'image' : 'video'

      // Create preview
      const reader = new FileReader()
      reader.onload = (e) => {
        this.selectedFilePreview = e.target.result
      }
      reader.readAsDataURL(file)
    },
    clearSelectedFile() {
      this.selectedFile = null
      this.selectedFileType = null
      this.selectedFilePreview = null
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },
    toggleGifPicker() {
      this.showGifPicker = !this.showGifPicker
      this.showAttachmentMenu = false
      if (this.showGifPicker && this.trendingGifs.length === 0) {
        this.loadTrendingGifs()
      }
    },
    async loadTrendingGifs() {
      this.loadingGifs = true
      try {
        const response = await fetch(
          `https://api.giphy.com/v1/gifs/trending?api_key=${this.giphyApiKey}&limit=30&rating=g`
        )
        const data = await response.json()
        this.trendingGifs = data.data.map(gif => ({
          id: gif.id,
          url: gif.images.fixed_height.url,
          title: gif.title
        }))
      } catch (error) {
        console.error('Error loading trending GIFs:', error)
        // Fallback to hardcoded GIFs
        this.trendingGifs = [
          { id: 1, url: 'https://media.giphy.com/media/3oz8xAFtqoOUUrsh7W/giphy.gif', title: 'Happy' },
          { id: 2, url: 'https://media.giphy.com/media/l0MYt5jPR6QX5pnqM/giphy.gif', title: 'Thumbs up' },
          { id: 3, url: 'https://media.giphy.com/media/g9582DNuQppxC/giphy.gif', title: 'Laughing' },
          { id: 4, url: 'https://media.giphy.com/media/26ufdipQqU2lhNA4g/giphy.gif', title: 'Yes' },
          { id: 5, url: 'https://media.giphy.com/media/3o7abKhOpu0NwenH3O/giphy.gif', title: 'No' },
          { id: 6, url: 'https://media.giphy.com/media/3o6Zt481isNVuQI1l6/giphy.gif', title: 'Thinking' }
        ]
      } finally {
        this.loadingGifs = false
      }
    },
    searchGifs() {
      // Clear previous timeout
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }

      // Debounce search
      this.searchTimeout = setTimeout(async () => {
        if (!this.gifSearchQuery.trim()) {
          this.searchedGifs = []
          return
        }

        this.loadingGifs = true
        try {
          const response = await fetch(
            `https://api.giphy.com/v1/gifs/search?api_key=${this.giphyApiKey}&q=${encodeURIComponent(this.gifSearchQuery)}&limit=30&rating=g`
          )
          const data = await response.json()
          this.searchedGifs = data.data.map(gif => ({
            id: gif.id,
            url: gif.images.fixed_height.url,
            title: gif.title
          }))
        } catch (error) {
          console.error('Error searching GIFs:', error)
          this.searchedGifs = []
        } finally {
          this.loadingGifs = false
        }
      }, 500) // Wait 500ms after user stops typing
    },
    async sendGif(gifUrl) {
      if (!this.conversationId) return

      this.isSending = true

      try {
        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages`,
          {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify({
              content: this.newMessage || '',
              authorId: this.currentUser.id,
              attachmentType: 'gif',
              attachmentUrl: gifUrl,
              attachmentName: 'GIF'
            })
          }
        )

        if (response.ok) {
          this.newMessage = ''
          this.showGifPicker = false
          await this.loadMessages()
        }
      } catch (error) {
        console.error('Error sending GIF:', error)
        alert('Błąd podczas wysyłania GIF')
      } finally {
        this.isSending = false
      }
    },
    handleDragOver(event) {
      event.preventDefault()
      this.isDragging = true
    },
    handleDragLeave(event) {
      event.preventDefault()
      this.isDragging = false
    },
    handleDrop(event) {
      event.preventDefault()
      this.isDragging = false

      const files = event.dataTransfer.files
      if (files.length > 0) {
        const file = files[0]

        // Check if file is image or video
        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
          // Simulate file input change
          const dataTransfer = new DataTransfer()
          dataTransfer.items.add(file)
          if (this.$refs.fileInput) {
            this.$refs.fileInput.files = dataTransfer.files
            this.handleFileSelect({ target: { files: dataTransfer.files } })
          }
        } else {
          alert('Obsługiwane są tylko pliki obrazów i wideo')
        }
      }
    },
    openImageModal(imageUrl) {
      this.modalImageUrl = imageUrl
      this.showImageModal = true
    },
    closeImageModal() {
      this.showImageModal = false
      this.modalImageUrl = ''
    },
    startEdit(message) {
      this.editingMessageId = message.id
      this.editMessageText = message.content
      // Focus input after Vue updates DOM
      this.$nextTick(() => {
        const refName = 'editInput' + message.id
        const input = this.$refs[refName]
        if (input) {
          if (Array.isArray(input)) {
            input[0].focus()
          } else {
            input.focus()
          }
        }
      })
    },
    async saveEdit(messageId) {
      try {
        const response = await fetch(`${this.apiUrl}/conversations/messages/${messageId}/edit`, {
          method: 'PUT',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            content: this.editMessageText,
            userId: this.currentUser.id
          })
        })

        if (response.ok) {
          const updatedMessage = await response.json()
          const message = this.messages.find(m => m.id === messageId)
          if (message) {
            message.content = updatedMessage.content
            message.edited = updatedMessage.edited
          }
        } else {
          alert('Nie udało się edytować wiadomości')
        }
      } catch (error) {
        console.error('Error editing message:', error)
        alert('Błąd podczas edycji wiadomości')
      }

      this.editingMessageId = null
      this.editMessageText = ''
    },
    cancelEdit() {
      this.editingMessageId = null
      this.editMessageText = ''
    },
    async deleteMessage(messageId) {
      if (!confirm('Czy na pewno chcesz usunąć tę wiadomość?')) {
        return
      }

      try {
        const response = await fetch(`${this.apiUrl}/conversations/messages/${messageId}/delete`, {
          method: 'DELETE',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            userId: this.currentUser.id
          })
        })

        if (response.ok) {
          const updatedMessage = await response.json()
          const message = this.messages.find(m => m.id === messageId)
          if (message) {
            message.deleted = updatedMessage.deleted
            message.content = updatedMessage.content
          }
        } else {
          alert('Nie udało się usunąć wiadomości')
        }
      } catch (error) {
        console.error('Error deleting message:', error)
        alert('Błąd podczas usuwania wiadomości')
      }
    },
    toggleReactionMenu(messageId, event) {
      event.stopPropagation()
      if (this.openReactionMenuId === messageId) {
        this.openReactionMenuId = null
      } else {
        this.openReactionMenuId = messageId
      }
    },
    handleClickOutside(event) {
      const reactionsMenu = event.target.closest('.reactions-menu')
      const toggleBtn = event.target.closest('.reaction-toggle-btn')

      if (!reactionsMenu && !toggleBtn) {
        this.openReactionMenuId = null
      }
    },
    hasActiveReactions(message) {
      if (!message.reactions) return false
      return Object.values(message.reactions).some(count => count > 0)
    },
    async addReaction(messageId, reactionType) {
      await this.toggleReaction(messageId, reactionType)
      this.openReactionMenuId = null
    },
    async toggleReaction(messageId, reactionType) {
      if (!this.conversationId) return

      try {
        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages/${messageId}/reaction`,
          {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify({
              type: reactionType
            })
          }
        )

        if (response.ok) {
          await this.loadMessages()
        }
      } catch (error) {
        console.error('Error adding reaction:', error)
      }
    },
    async markConversationAsRead() {
      if (!this.conversationId) return

      try {
        await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/messages/mark-read`,
          {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify({
              userId: this.currentUser.id
            })
          }
        )
      } catch (error) {
        console.error('Error marking messages as read:', error)
      }
    },
    formatTime(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`
    },
    getMessageDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      // Return date in format YYYY-MM-DD for grouping
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    },
    getDateLabel(dateString) {
      const date = new Date(dateString)
      const now = new Date()

      // Reset time part for comparison
      const dateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate())
      const nowOnly = new Date(now.getFullYear(), now.getMonth(), now.getDate())

      const diffMs = nowOnly - dateOnly
      const diffDays = Math.floor(diffMs / 86400000)

      if (diffDays === 0) {
        return 'Dziś'
      } else if (diffDays === 1) {
        return 'Wczoraj'
      } else {
        // Format: DD.MM.YYYY
        return `${String(date.getDate()).padStart(2, '0')}.${String(date.getMonth() + 1).padStart(2, '0')}.${date.getFullYear()}`
      }
    },
    isUserAtBottom() {
      const chatArea = this.$refs.chatArea
      if (!chatArea) return true

      // Consider user at bottom if within 100px of the bottom
      const threshold = 100
      const position = chatArea.scrollTop + chatArea.clientHeight
      const bottom = chatArea.scrollHeight

      return position >= bottom - threshold
    },
    scrollToBottom() {
      const chatArea = this.$refs.chatArea
      if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight
      }
    },
    loadChatCustomization() {
      const key = `chat_customization_${this.conversationId}`
      const saved = localStorage.getItem(key)
      if (saved) {
        this.chatCustomization = JSON.parse(saved)
      } else {
        this.chatCustomization = { ...this.defaultColors }
      }
    },
    savePersonalization() {
      const key = `chat_customization_${this.conversationId}`
      localStorage.setItem(key, JSON.stringify(this.chatCustomization))
      this.showPersonalizeModal = false
    },
    resetColor(colorKey) {
      this.chatCustomization[colorKey] = this.defaultColors[colorKey]
    },
    resetFontSize() {
      this.chatCustomization.fontSize = this.defaultColors.fontSize
    },
    closePersonalizeModal() {
      this.showPersonalizeModal = false
      // Reload customization in case user made changes but didn't save
      this.loadChatCustomization()
    },
    getMessageBubbleStyle(isSent) {
      const style = {
        fontSize: this.chatCustomization.fontSize + 'px'
      }

      if (isSent) {
        style.backgroundColor = this.chatCustomization.myMessageColor
      } else {
        style.backgroundColor = this.chatCustomization.friendMessageColor
      }

      return style
    },
    getChatAreaStyle() {
      if (this.chatCustomization.backgroundColor !== 'transparent') {
        return {
          backgroundColor: this.chatCustomization.backgroundColor
        }
      }
      return {}
    },
    sendTypingNotification() {
      if (!this.conversationId) return

      // Debounce typing notifications - send faster for quicker response
      if (this.typingTimeout) {
        clearTimeout(this.typingTimeout)
      }

      this.typingTimeout = setTimeout(async () => {
        try {
          await fetch(`${this.apiUrl}/conversations/${this.conversationId}/typing`, {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: JSON.stringify({
              userId: this.currentUser.id
            })
          })
        } catch (error) {
          console.error('Error sending typing notification:', error)
        }
      }, 150)
    },
    async clearTypingStatus() {
      if (!this.conversationId) return

      try {
        await fetch(`${this.apiUrl}/conversations/${this.conversationId}/typing/clear`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            userId: this.currentUser.id
          })
        })
      } catch (error) {
        console.error('Error clearing typing status:', error)
      }
    },
    async checkTypingStatus() {
      if (!this.conversationId) return

      try {
        const response = await fetch(
          `${this.apiUrl}/conversations/${this.conversationId}/typing-status?userId=${this.currentUser.id}`,
          {
            headers: this.getAuthHeaders()
          }
        )

        if (response.ok) {
          const data = await response.json()
          this.isTyping = data.isTyping
          if (data.isTyping && data.username) {
            this.typingUsername = data.username
          }
        }
      } catch (error) {
        console.error('Error checking typing status:', error)
      }
    }
  }
}
</script>

<style scoped>
.chatroom-container {
  width: 100%;
  max-width: 1200px;
  height: 90vh;
  background: var(--color-cardBg);
  backdrop-filter: blur(10px);
  border: 2px solid var(--color-border);
  border-radius: 20px;
  box-shadow: 0 20px 60px var(--color-primary);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin: 0 auto;
}

.chatroom-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  padding: 20px 25px;
  display: flex;
  align-items: center;
  gap: 20px;
  text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
  border-bottom: 2px solid var(--color-border);
}

.back-btn {
  padding: 10px 20px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: white;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.back-btn:hover {
  background: rgba(0, 0, 0, 0.5);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateX(-3px);
}

.chat-user-info {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
}

.chat-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.chat-username {
  font-size: 22px;
  font-weight: bold;
}

.chat-username .real-username {
  color: var(--color-textLight);
  font-size: 16px;
  font-weight: normal;
  font-style: italic;
  margin-left: 4px;
}

.header-spacer {
  width: 120px;
}

.personalize-btn {
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: white;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.personalize-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: scale(1.05);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(5px);
}

.personalize-modal {
  background: var(--color-cardBg);
  border: 2px solid var(--color-border);
  border-radius: 20px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  padding: 20px 25px;
  border-radius: 18px 18px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 22px;
  font-weight: bold;
}

.modal-close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  color: white;
  font-size: 20px;
  width: 35px;
  height: 35px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.modal-body {
  padding: 25px;
}

.color-section {
  margin-bottom: 20px;
}

.color-section label {
  display: block;
  color: var(--color-text);
  font-weight: 600;
  margin-bottom: 10px;
  font-size: 15px;
}

.color-picker-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-picker-row input[type="color"] {
  width: 60px;
  height: 40px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
}

.color-preview {
  width: 40px;
  height: 40px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
}

.font-size-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.font-size-slider {
  flex: 1;
  height: 6px;
  border-radius: 3px;
  background: var(--color-border);
  outline: none;
  -webkit-appearance: none;
  cursor: pointer;
}

.font-size-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--color-primary);
  cursor: pointer;
  transition: all 0.2s;
}

.font-size-slider::-webkit-slider-thumb:hover {
  background: var(--color-primaryDark);
  transform: scale(1.2);
}

.font-size-slider::-moz-range-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--color-primary);
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.font-size-slider::-moz-range-thumb:hover {
  background: var(--color-primaryDark);
  transform: scale(1.2);
}

.reset-btn {
  padding: 8px 16px;
  background: var(--color-cardBg);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-text);
  font-size: 13px;
  cursor: pointer;
  transition: all 0.3s;
}

.reset-btn:hover {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 25px;
  justify-content: flex-end;
}

.save-btn {
  padding: 12px 24px;
  background: var(--color-primary);
  border: 2px solid var(--color-primary);
  border-radius: 10px;
  color: white;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.save-btn:hover {
  background: var(--color-primaryDark);
  transform: scale(1.05);
}

.cancel-btn {
  padding: 12px 24px;
  background: var(--color-cardBg);
  border: 2px solid var(--color-border);
  border-radius: 10px;
  color: var(--color-text);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.cancel-btn:hover {
  background: var(--color-border);
}

.chat-area {
  flex: 1;
  padding: 25px;
  overflow-y: auto;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.5) 100%);
}

.empty-chat-message {
  text-align: center;
  color: var(--color-textLight);
  font-size: 16px;
  padding: 40px 20px;
  opacity: 0.8;
}

.message {
  margin-bottom: 20px;
  animation: slideIn 0.3s ease-out;
  position: relative;
}

.message:hover {
  /* Usunięto hover effect */
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message.sent {
  text-align: right;
}

.message.received {
  text-align: left;
}

.message-wrapper {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  max-width: 75%;
  position: relative;
}

.message.sent .message-wrapper {
  flex-direction: row-reverse;
}

.message-bubble {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 14px;
  word-wrap: break-word;
  position: relative;
  line-height: 1.4;
  transition: padding 0.2s ease;
}

.message-content {
  display: inline-block;
}

.message.sent .message-bubble {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border-bottom-right-radius: 4px;
  box-shadow: 0 4px 15px var(--color-border);
}

.message.received .message-bubble {
  background: rgba(255, 255, 255, 0.1);
  color: #e0e7ff;
  border: 1px solid var(--color-border);
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.message-time {
  font-size: 11px;
  color: var(--color-textLight);
  margin-top: 5px;
  display: inline-block;
}

/* Date separator */
.date-separator {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 30px 0 20px 0;
  position: relative;
}

.date-separator::before,
.date-separator::after {
  content: '';
  flex: 1;
  height: 1px;
  background: linear-gradient(to right, transparent, var(--color-border), transparent);
}

.date-separator::before {
  margin-right: 15px;
}

.date-separator::after {
  margin-left: 15px;
}

.date-label {
  padding: 6px 16px;
  background: var(--color-cardBg);
  border: 1px solid var(--color-border);
  border-radius: 20px;
  color: var(--color-textLight);
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.1);
  backdrop-filter: blur(10px);
}

/* Typing indicator */
.typing-indicator {
  padding: 10px 25px;
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--color-cardBg);
  border-top: 1px solid var(--color-border);
  animation: fadeIn 0.3s ease;
}

.typing-text {
  font-size: 13px;
  color: var(--color-textLight);
  font-style: italic;
}

.typing-dots {
  display: flex;
  gap: 4px;
  align-items: center;
}

.typing-dots .dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--color-primary);
  animation: typingDot 1.4s infinite;
}

.typing-dots .dot:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-dots .dot:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typingDot {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.4;
  }
  30% {
    transform: translateY(-8px);
    opacity: 1;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Message controls container */
.message-controls {
  display: flex;
  align-items: center;
  gap: 6px;
  opacity: 0;
  transition: opacity 0.2s ease;
  pointer-events: none;
}

.message-wrapper:hover .message-controls {
  opacity: 1;
  pointer-events: auto;
}

/* Przycisk + do rozwijania reakcji */
.reaction-toggle-btn {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.reaction-toggle-btn:hover {
  background: rgba(0, 0, 0, 0.7);
  border-color: var(--color-primaryLight);
  transform: scale(1.15);
}

/* Aktywne reakcje (te z liczbą > 0) */
.active-reactions {
  display: inline-flex;
  gap: 6px;
  margin-top: 6px;
  flex-wrap: wrap;
}

.reaction-badge {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 3px 8px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: white;
}

.reaction-badge:hover {
  background: var(--color-primary);
  border-color: var(--color-primaryLight);
  transform: scale(1.05);
}

/* Menu rozwijane z wszystkimi reakcjami */
.reactions-menu {
  position: absolute;
  bottom: -50px;
  background: rgba(0, 0, 0, 0.95);
  border: 2px solid var(--color-border);
  border-radius: 15px;
  padding: 8px;
  display: flex;
  gap: 6px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  z-index: 10;
  animation: slideDown 0.2s ease-out;
}

/* Position reactions menu for sent messages (right-aligned) */
.message.sent .reactions-menu {
  right: 0;
}

/* Position reactions menu for received messages (left-aligned) */
.message.received .reactions-menu {
  left: 0;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.reactions-menu .reaction-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid var(--color-border);
  border-radius: 50%;
  width: 40px;
  height: 40px;
  cursor: pointer;
  font-size: 20px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.reactions-menu .reaction-btn:hover {
  transform: scale(1.15);
  border-color: var(--color-primaryLight);
  background: var(--color-primary);
}

.reactions-menu .reaction-btn.active {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  border-color: var(--color-primaryLight);
  box-shadow: 0 2px 10px var(--color-primary);
}

/* Message actions (edit/delete) */
.message-actions {
  display: inline-flex;
  gap: 4px;
}

.action-btn {
  background: rgba(0, 0, 0, 0.6);
  border: 2px solid #000000;
  border-radius: 8px;
  width: 28px;
  height: 28px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  padding: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
  flex-shrink: 0;
}

.action-btn:hover {
  background: var(--color-primary);
  border-color: #000000;
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6);
}

/* Edit message form */
.edit-message-form {
  display: flex;
  gap: 6px;
  align-items: center;
  width: 100%;
}

.edit-input {
  flex: 1;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.edit-input:focus {
  outline: none;
  border-color: var(--color-primaryLight);
}

.save-edit-btn,
.cancel-edit-btn {
  background: var(--color-primary);
  border: none;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.cancel-edit-btn {
  background: rgba(239, 68, 68, 0.3);
}

.save-edit-btn:hover {
  background: var(--color-primaryLight);
  transform: scale(1.1);
}

.cancel-edit-btn:hover {
  background: rgba(239, 68, 68, 0.5);
  transform: scale(1.1);
}

/* Edited and deleted labels */
.edited-label {
  font-size: 11px;
  opacity: 0.6;
  font-style: italic;
  margin-left: 4px;
}

.deleted-message {
  font-style: italic;
  opacity: 0.5;
  color: var(--color-textLight);
}

.input-area {
  padding: 25px;
  background: rgba(0, 0, 0, 0.4);
  border-top: 2px solid var(--color-border);
  display: flex;
  gap: 12px;
  align-items: flex-end;
  position: relative;
  flex-wrap: wrap;
}

.attachment-btn {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: var(--color-primary);
  border: 2px solid var(--color-border);
  color: white;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.attachment-btn:hover {
  background: var(--color-primaryLight);
  transform: scale(1.1);
}

.attachment-menu {
  position: absolute;
  bottom: 80px;
  left: 25px;
  background: rgba(0, 0, 0, 0.95);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  padding: 8px;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.2s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.menu-item {
  display: block;
  width: 100%;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: white;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 6px;
  text-align: left;
}

.menu-item:last-child {
  margin-bottom: 0;
}

.menu-item:hover {
  background: var(--color-primary);
  border-color: var(--color-primaryLight);
}

.gif-picker {
  position: absolute;
  bottom: 80px;
  left: 25px;
  width: 400px;
  max-height: 400px;
  background: rgba(0, 0, 0, 0.95);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  padding: 15px;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.2s ease-out;
}

.gif-picker-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--color-border);
  gap: 10px;
}

.gif-search-input {
  flex: 1;
  padding: 10px 15px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-border);
  border-radius: 20px;
  color: white;
  font-size: 14px;
  outline: none;
  transition: all 0.3s;
}

.gif-search-input:focus {
  border-color: var(--color-primaryLight);
  box-shadow: 0 0 10px rgba(139, 92, 246, 0.3);
}

.gif-search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.gif-picker-header .close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.gif-picker-header .close-btn:hover {
  color: var(--color-primaryLight);
  transform: scale(1.2);
}

.loading-gifs {
  text-align: center;
  color: var(--color-text);
  padding: 40px 20px;
  font-size: 16px;
}

.no-gifs {
  text-align: center;
  color: var(--color-textLight);
  padding: 40px 20px;
  font-size: 14px;
  font-style: italic;
}

.gif-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  max-height: 320px;
  overflow-y: auto;
}

.gif-item {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  border: 2px solid transparent;
}

.gif-item:hover {
  border-color: var(--color-primaryLight);
  transform: scale(1.05);
}

.file-preview {
  position: relative;
  width: 120px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid var(--color-border);
}

.preview-image,
.preview-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-preview .clear-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  border: none;
  color: white;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.file-preview .clear-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.upload-progress {
  position: absolute;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--color-primary);
  color: white;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.drop-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(139, 92, 246, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: white;
  font-weight: bold;
  z-index: 50;
  backdrop-filter: blur(5px);
}

.chat-area.drag-over {
  border: 3px dashed var(--color-primaryLight);
}

.message-attachment {
  margin-bottom: 8px;
}

.attachment-image,
.attachment-gif {
  max-width: 300px;
  max-height: 300px;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s;
  display: block;
}

.attachment-image:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
}

.attachment-video {
  max-width: 300px;
  max-height: 300px;
  border-radius: 12px;
  display: block;
}

.image-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90%;
}

.modal-content img {
  max-width: 100%;
  max-height: 90vh;
  border-radius: 8px;
  box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
}

.modal-close {
  position: absolute;
  top: -40px;
  right: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.modal-close:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.input-area input {
  flex: 1;
  padding: 14px 20px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-border);
  border-radius: 25px;
  font-size: 16px;
  color: white;
  outline: none;
  transition: all 0.3s;
}

.input-area input::placeholder {
  color: var(--color-textLight);
}

.input-area input:focus {
  border-color: var(--color-primaryLight);
  box-shadow: 0 0 20px var(--color-border);
}

.input-area button {
  padding: 14px 28px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border: none;
  border-radius: 25px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px var(--color-primary);
}

.input-area button:hover {
  transform: scale(1.05);
}

.input-area button:active {
  transform: scale(0.95);
}

.chat-area::-webkit-scrollbar {
  width: 8px;
}

.chat-area::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.chat-area::-webkit-scrollbar-thumb {
  background: var(--color-primary);
  border-radius: 4px;
}

.chat-area::-webkit-scrollbar-thumb:hover {
  background: var(--color-primaryLight);
}

/* Responsive Design */
@media (max-width: 768px) {
  .chatroom-container {
    height: 85vh;
    max-width: 100%;
    border-radius: 15px;
  }

  .chatroom-header {
    padding: 15px 20px;
    gap: 15px;
  }

  .back-btn {
    padding: 8px 15px;
    font-size: 13px;
  }

  .chat-avatar {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  .chat-username {
    font-size: 20px;
  }

  .header-spacer {
    width: 80px;
  }

  .chat-area {
    padding: 20px;
  }

  .message-wrapper {
    max-width: 85%;
  }

  .message-bubble {
    padding: 5px 12px;
  }

  .reaction-toggle-btn {
    width: 22px;
    height: 22px;
    font-size: 16px;
  }

  .action-btn {
    width: 26px;
    height: 26px;
    font-size: 12px;
  }

  .reaction-badge {
    font-size: 13px;
    padding: 2px 6px;
  }

  .reactions-menu .reaction-btn {
    width: 36px;
    height: 36px;
    font-size: 18px;
  }

  .input-area {
    padding: 20px;
    gap: 10px;
  }

  .input-area input {
    padding: 12px 18px;
    font-size: 15px;
  }

  .input-area button {
    padding: 12px 24px;
    font-size: 15px;
  }
}

@media (max-width: 480px) {
  .chatroom-container {
    height: 80vh;
    border-radius: 12px;
  }

  .chatroom-header {
    padding: 12px 15px;
    gap: 10px;
  }

  .back-btn {
    padding: 6px 12px;
    font-size: 12px;
  }

  .chat-avatar {
    width: 35px;
    height: 35px;
    font-size: 16px;
  }

  .chat-username {
    font-size: 18px;
  }

  .header-spacer {
    display: none;
  }

  .chat-area {
    padding: 15px;
  }

  .message-wrapper {
    max-width: 90%;
    gap: 6px;
  }

  .message-bubble {
    padding: 4px 10px;
  }

  .message-time {
    font-size: 10px;
  }

  .reaction-toggle-btn {
    width: 20px;
    height: 20px;
    font-size: 14px;
  }

  .action-btn {
    width: 24px;
    height: 24px;
    font-size: 11px;
  }

  .reaction-badge {
    font-size: 12px;
    padding: 2px 5px;
  }

  .reactions-menu {
    padding: 6px;
    gap: 4px;
  }

  .reactions-menu .reaction-btn {
    width: 32px;
    height: 32px;
    font-size: 16px;
  }

  .input-area {
    padding: 15px;
    gap: 8px;
    flex-wrap: wrap;
  }

  .input-area input {
    padding: 10px 16px;
    font-size: 14px;
    border-radius: 20px;
  }

  .input-area button {
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 20px;
  }
}
</style>
