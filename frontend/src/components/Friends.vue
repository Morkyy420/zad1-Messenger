<template>
  <div class="friends-container">
    <div class="friends-panel">
      <div class="panel-header">
        <h2>👥 Znajomi</h2>
        <button class="add-btn" @click="showAllUsers = !showAllUsers">
          {{ showAllUsers ? '❌ Zamknij' : '➕ Dodaj znajomego' }}
        </button>
      </div>

      <div v-if="showAllUsers" class="all-users-section">
        <h3>Wszyscy użytkownicy</h3>
        <div v-if="allUsers.length === 0" class="empty-state">
          Brak użytkowników
        </div>
        <div v-else class="users-list">
          <div
            v-for="user in availableUsers"
            :key="user.id"
            class="user-card"
          >
            <div class="user-avatar">{{ user.username.charAt(0).toUpperCase() }}</div>
            <div class="user-info">
              <div class="user-name">{{ user.username }}</div>
              <div class="user-email">{{ user.email }}</div>
            </div>
            <button
              v-if="!isFriend(user.id)"
              class="add-friend-btn"
              @click="addFriend(user.id)"
            >
              ➕ Dodaj
            </button>
            <span v-else class="friend-badge">✓ Znajomy</span>
          </div>
        </div>
      </div>

      <div class="friends-section">
        <h3>Twoi znajomi ({{ friends.length }})</h3>
        <div v-if="friends.length === 0" class="empty-state">
          Nie masz jeszcze znajomych. Dodaj kogoś!
        </div>
        <div v-else class="friends-list">
          <div
            v-for="friend in friendsWithMessages"
            :key="friend.id"
            class="friend-card"
            @click="startChat(friend)"
          >
            <div class="friend-avatar">{{ friend.username.charAt(0).toUpperCase() }}</div>
            <div class="friend-info">
              <div class="friend-name-section">
                <div v-if="editingNickId === friend.id" class="nick-edit">
                  <input
                    v-model="editedNick"
                    type="text"
                    placeholder="Pseudonim"
                    @keyup.enter="saveNick(friend.id)"
                    @keyup.esc="cancelEditNick"
                    ref="nickInput"
                  />
                  <button class="save-nick-btn" @click.stop="saveNick(friend.id)" title="Zapisz">✓</button>
                  <button class="cancel-nick-btn" @click.stop="cancelEditNick" title="Anuluj">✕</button>
                </div>
                <div v-else class="friend-name" @dblclick="startEditNick(friend)">
                  <span v-if="friend.nickname">
                    {{ friend.nickname }} <span class="real-username">({{ friend.username }})</span>
                  </span>
                  <span v-else>{{ friend.username }}</span>
                  <button class="edit-nick-btn" @click.stop="startEditNick(friend)" title="Edytuj pseudonim">✏️</button>
                </div>
              </div>
              <div v-if="friend.lastMessage" class="last-message">
                <span class="message-text">{{ friend.lastMessage.content }}</span>
                <div class="message-meta">
                  <span class="message-time">{{ formatTime(friend.lastMessage.createdAt) }}</span>
                  <span class="message-date">{{ formatDate(friend.lastMessage.createdAt) }}</span>
                  <span v-if="friend.lastMessage.authorId !== currentUser.id" class="read-status">
                    {{ friend.lastMessage.isRead ? '✓✓' : '✓' }}
                  </span>
                </div>
              </div>
              <div v-else class="last-message no-messages">
                Brak wiadomości
              </div>
            </div>
            <div class="friend-actions" @click.stop>
              <button class="chat-btn" @click="startChat(friend)">💬 Czat</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { generateConversationKey, decryptMessageForConversation } from '@/utils/encryption'

export default {
  name: 'Friends',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      friends: [],
      allUsers: [],
      conversations: [],
      showAllUsers: false,
      editingNickId: null,
      editedNick: '',
      nicknames: JSON.parse(localStorage.getItem('friendNicknames') || '{}'),
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api'
    }
  },
  computed: {
    availableUsers() {
      return this.allUsers.filter(user => user.id !== this.currentUser?.id)
    },
    friendsWithMessages() {
      return this.friends.map(friend => {
        const conversation = this.conversations.find(
          conv => conv.participant.id === friend.id
        )

        let lastMessage = conversation?.lastMessage || null

        // Decrypt the last message if it exists
        if (lastMessage && lastMessage.content && conversation) {
          const conversationKey = generateConversationKey(
            conversation.id,
            this.currentUser.id,
            friend.id
          )
          lastMessage = {
            ...lastMessage,
            content: decryptMessageForConversation(lastMessage.content, conversationKey)
          }
        }

        return {
          ...friend,
          nickname: this.nicknames[friend.id] || null,
          lastMessage: lastMessage
        }
      })
    }
  },
  mounted() {
    this.loadFriends()
    this.loadAllUsers()
    this.loadConversations()
    // Refresh conversations every 3 seconds for real-time updates
    this.conversationInterval = setInterval(() => {
      this.loadConversations()
    }, 3000)
  },
  beforeUnmount() {
    if (this.conversationInterval) {
      clearInterval(this.conversationInterval)
    }
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      if (!token) {
        console.error('[Friends] No token found - forcing logout')
        localStorage.clear()
        this.$router.push('/login')
        return {}
      }
      return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    },
    async loadFriends() {
      try {
        const response = await fetch(`${this.apiUrl}/auth/users/${this.currentUser.id}/friends`, {
          headers: this.getAuthHeaders()
        })
        if (response.ok) {
          this.friends = await response.json()
        }
      } catch (error) {
        console.error('Error loading friends:', error)
      }
    },
    async loadAllUsers() {
      try {
        const response = await fetch(`${this.apiUrl}/auth/users`, {
          headers: this.getAuthHeaders()
        })
        if (response.ok) {
          this.allUsers = await response.json()
        }
      } catch (error) {
        console.error('Error loading users:', error)
      }
    },
    async loadConversations() {
      try {
        const response = await fetch(`${this.apiUrl}/conversations/${this.currentUser.id}`, {
          headers: this.getAuthHeaders()
        })
        if (response.ok) {
          this.conversations = await response.json()
        }
      } catch (error) {
        console.error('Error loading conversations:', error)
      }
    },
    async addFriend(friendId) {
      try {
        const response = await fetch(`${this.apiUrl}/auth/users/${friendId}/friends`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            currentUserId: this.currentUser.id
          })
        })

        if (response.ok) {
          await this.loadFriends()
          await this.loadAllUsers()
        }
      } catch (error) {
        console.error('Error adding friend:', error)
      }
    },
    isFriend(userId) {
      return this.friends.some(friend => friend.id === userId)
    },
    startChat(friend) {
      this.$emit('open-chat', friend)
    },
    formatTime(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return `${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`
    },
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      const today = new Date()
      const yesterday = new Date(today)
      yesterday.setDate(yesterday.getDate() - 1)

      if (date.toDateString() === today.toDateString()) {
        return 'Dzisiaj'
      } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Wczoraj'
      } else {
        return `${date.getDate()}.${date.getMonth() + 1}.${date.getFullYear()}`
      }
    },
    startEditNick(friend) {
      this.editingNickId = friend.id
      this.editedNick = this.nicknames[friend.id] || ''
      this.$nextTick(() => {
        if (this.$refs.nickInput && this.$refs.nickInput[0]) {
          this.$refs.nickInput[0].focus()
        }
      })
    },
    saveNick(friendId) {
      if (this.editedNick.trim()) {
        this.nicknames[friendId] = this.editedNick.trim()
      } else {
        delete this.nicknames[friendId]
      }
      localStorage.setItem('friendNicknames', JSON.stringify(this.nicknames))
      this.editingNickId = null
      this.editedNick = ''
    },
    cancelEditNick() {
      this.editingNickId = null
      this.editedNick = ''
    }
  }
}
</script>

<style scoped>
.friends-container {
  width: 100%;
  max-width: 1200px;
  height: 90vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.friends-panel {
  width: 100%;
  height: 100%;
  background: var(--color-cardBg);
  backdrop-filter: blur(10px);
  border: 2px solid var(--color-border);
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 20px 60px var(--color-primary);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 20px;
  border-bottom: 2px solid var(--color-border);
}

.panel-header h2 {
  color: var(--color-primaryLight);
  font-size: 28px;
  text-shadow: 0 0 20px var(--color-primaryLight);
  margin: 0;
}

.add-btn {
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px var(--color-primary);
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--color-primary);
}

.all-users-section,
.friends-section {
  margin-bottom: 25px;
}

.all-users-section {
  max-height: 40%;
  overflow-y: auto;
  padding-bottom: 20px;
  border-bottom: 2px solid var(--color-border);
}

.friends-section {
  flex: 1;
  overflow-y: auto;
}

h3 {
  color: var(--color-text);
  font-size: 18px;
  margin-bottom: 15px;
}

.empty-state {
  text-align: center;
  color: var(--color-textLight);
  padding: 40px;
  font-style: italic;
}

.users-list,
.friends-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.user-card,
.friend-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  transition: all 0.3s;
}

.user-card:hover,
.friend-card:hover {
  border-color: var(--color-primaryLight);
  background: var(--color-primary);
  transform: translateX(5px);
  cursor: pointer;
}

.user-avatar,
.friend-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 20px;
  flex-shrink: 0;
}

.user-info,
.friend-info {
  flex: 1;
}

.user-name,
.friend-name {
  color: var(--color-text);
  font-weight: 600;
  font-size: 16px;
  margin-bottom: 4px;
}

.friend-name-section {
  position: relative;
}

.friend-name {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.real-username {
  color: var(--color-textLight);
  font-size: 14px;
  font-weight: normal;
  font-style: italic;
}

.edit-nick-btn {
  background: none;
  border: none;
  color: var(--color-textLight);
  font-size: 14px;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s;
  opacity: 0.6;
}

.edit-nick-btn:hover {
  opacity: 1;
  background: rgba(139, 92, 246, 0.2);
}

.nick-edit {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nick-edit input {
  flex: 1;
  padding: 6px 10px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-primaryLight);
  border-radius: 8px;
  color: var(--color-text);
  font-size: 15px;
  font-weight: 600;
  outline: none;
  transition: all 0.3s;
}

.nick-edit input:focus {
  box-shadow: 0 0 10px rgba(139, 92, 246, 0.3);
}

.save-nick-btn,
.cancel-nick-btn {
  padding: 6px 12px;
  background: none;
  border: 2px solid;
  border-radius: 8px;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.save-nick-btn {
  border-color: #22c55e;
  color: #22c55e;
}

.save-nick-btn:hover {
  background: #22c55e;
  color: white;
  transform: scale(1.1);
}

.cancel-nick-btn {
  border-color: #ef4444;
  color: #ef4444;
}

.cancel-nick-btn:hover {
  background: #ef4444;
  color: white;
  transform: scale(1.1);
}

.user-email,
.friend-email {
  color: var(--color-textLight);
  font-size: 14px;
}

.last-message {
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid var(--color-border);
}

.message-text {
  display: block;
  color: var(--color-text);
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 300px;
  margin-bottom: 4px;
}

.message-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
}

.message-time {
  color: var(--color-textLight);
  font-weight: 600;
}

.message-date {
  color: var(--color-textLight);
}

.read-status {
  color: var(--color-success);
  font-weight: bold;
  margin-left: auto;
}

.no-messages {
  color: var(--color-textLight);
  font-style: italic;
  font-size: 13px;
  border: none;
  padding: 0;
  margin-top: 4px;
}

.add-friend-btn,
.chat-btn {
  padding: 8px 16px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  white-space: nowrap;
}

.add-friend-btn:hover,
.chat-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 15px var(--color-primary);
}

.friend-badge {
  padding: 8px 16px;
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.5);
  border-radius: 8px;
  color: #86efac;
  font-size: 13px;
  font-weight: bold;
  white-space: nowrap;
}

.friend-actions {
  display: flex;
  gap: 8px;
}

/* Scrollbar styling */
.all-users-section::-webkit-scrollbar,
.friends-section::-webkit-scrollbar {
  width: 8px;
}

.all-users-section::-webkit-scrollbar-track,
.friends-section::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.all-users-section::-webkit-scrollbar-thumb,
.friends-section::-webkit-scrollbar-thumb {
  background: var(--color-primary);
  border-radius: 4px;
}

.all-users-section::-webkit-scrollbar-thumb:hover,
.friends-section::-webkit-scrollbar-thumb:hover {
  background: var(--color-primaryLight);
}

/* Responsive Design */
@media (max-width: 768px) {
  .friends-panel {
    padding: 20px;
    height: auto;
    max-height: none;
  }

  .panel-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .panel-header h2 {
    font-size: 24px;
    text-align: center;
  }

  .add-btn {
    width: 100%;
  }

  .all-users-section {
    max-height: none;
  }

  .user-card,
  .friend-card {
    padding: 12px;
    gap: 12px;
  }

  .user-avatar,
  .friend-avatar {
    width: 45px;
    height: 45px;
    font-size: 18px;
  }

  .user-name,
  .friend-name {
    font-size: 15px;
  }

  .user-email {
    font-size: 13px;
  }

  .message-text {
    max-width: 200px;
  }
}

@media (max-width: 480px) {
  .friends-container {
    height: auto;
  }

  .friends-panel {
    padding: 15px;
    border-radius: 15px;
  }

  .panel-header h2 {
    font-size: 20px;
  }

  h3 {
    font-size: 16px;
  }

  .user-card,
  .friend-card {
    padding: 10px;
    gap: 10px;
  }

  .user-avatar,
  .friend-avatar {
    width: 40px;
    height: 40px;
    font-size: 16px;
  }

  .user-name,
  .friend-name {
    font-size: 14px;
  }

  .user-email {
    font-size: 12px;
  }

  .message-text {
    font-size: 13px;
    max-width: 150px;
  }

  .message-meta {
    font-size: 11px;
  }

  .add-friend-btn,
  .chat-btn {
    padding: 6px 12px;
    font-size: 12px;
  }

  .friend-badge {
    padding: 6px 12px;
    font-size: 12px;
  }
}
</style>
