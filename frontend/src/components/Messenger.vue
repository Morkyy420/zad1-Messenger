<template>
  <div class="messenger-container">
    <div class="messenger-header">
      💬 Messenger
    </div>

    <div class="chat-area" ref="chatArea">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="['message', message.author?.username === 'TestUser' ? 'sent' : 'received']"
      >
        <div class="message-bubble">
          {{ message.content }}
        </div>
        <div class="message-time">
          {{ formatTime(message.createdAt) }}
        </div>
        <div class="reactions-bar">
          <button
            v-for="(emoji, type) in reactionEmojis"
            :key="type"
            class="reaction-btn"
            :class="{ active: message.reactions && message.reactions[type] > 0 }"
            @click="toggleReaction(message.id, type)"
          >
            {{ emoji }} <span class="reaction-count">{{ message.reactions?.[type] || 0 }}</span>
          </button>
        </div>
      </div>
    </div>

    <div class="input-area">
      <input
        v-model="newMessage"
        type="text"
        placeholder="Napisz wiadomość..."
        @keypress.enter="sendMessage"
      />
      <button @click="sendMessage">Wyślij</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Messenger',
  data() {
    return {
      messages: [],
      newMessage: '',
      reactionEmojis: {
        heart: '❤️',
        like: '👍',
        laugh: '😂',
        wow: '😮'
      },
      apiUrl: 'http://localhost:8000/api'
    }
  },
  mounted() {
    this.loadMessages()
  },
  methods: {
    async loadMessages() {
      try {
        const response = await fetch(`${this.apiUrl}/messages`)
        const data = await response.json()
        this.messages = data
        this.$nextTick(() => {
          this.scrollToBottom()
        })
      } catch (error) {
        console.error('Błąd podczas ładowania wiadomości:', error)
      }
    },
    async sendMessage() {
      if (!this.newMessage.trim()) return

      try {
        const response = await fetch(`${this.apiUrl}/messages`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            content: this.newMessage
          })
        })

        if (response.ok) {
          this.newMessage = ''
          await this.loadMessages()
        }
      } catch (error) {
        console.error('Błąd podczas wysyłania wiadomości:', error)
      }
    },
    async toggleReaction(messageId, reactionType) {
      try {
        const response = await fetch(`${this.apiUrl}/messages/${messageId}/reaction`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            type: reactionType
          })
        })

        if (response.ok) {
          await this.loadMessages()
        }
      } catch (error) {
        console.error('Błąd podczas dodawania reakcji:', error)
      }
    },
    formatTime(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return `${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`
    },
    scrollToBottom() {
      const chatArea = this.$refs.chatArea
      if (chatArea) {
        chatArea.scrollTop = chatArea.scrollHeight
      }
    }
  }
}
</script>

<style scoped>
.messenger-container {
  width: 100%;
  max-width: 1200px;
  height: 90vh;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  margin: 0 auto;
}

.messenger-header {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  color: white;
  padding: 25px;
  text-align: center;
  font-size: 28px;
  font-weight: bold;
  text-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
  border-bottom: 2px solid rgba(139, 92, 246, 0.5);
}

.chat-area {
  flex: 1;
  padding: 25px;
  overflow-y: auto;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.5) 100%);
}

.message {
  margin-bottom: 20px;
  animation: slideIn 0.3s ease-out;
  position: relative;
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

.message-bubble {
  display: inline-block;
  max-width: 70%;
  padding: 12px 16px;
  border-radius: 18px;
  word-wrap: break-word;
  position: relative;
  margin-bottom: 5px;
}

.message.sent .message-bubble {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  color: white;
  border-bottom-right-radius: 4px;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.message.received .message-bubble {
  background: rgba(255, 255, 255, 0.1);
  color: #e0e7ff;
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-bottom-left-radius: 4px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}

.message-time {
  font-size: 11px;
  color: rgba(196, 181, 253, 0.7);
  margin-top: 5px;
  display: inline-block;
}

.reactions-bar {
  display: inline-flex;
  gap: 5px;
  margin-top: 5px;
  flex-wrap: wrap;
}

.reaction-btn {
  background: rgba(0, 0, 0, 0.4);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 15px;
  padding: 4px 10px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.reaction-btn:hover {
  transform: scale(1.1);
  border-color: #a78bfa;
  background: rgba(139, 92, 246, 0.2);
}

.reaction-btn.active {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  border-color: #a78bfa;
  color: white;
  box-shadow: 0 2px 10px rgba(139, 92, 246, 0.4);
}

.reaction-count {
  font-size: 12px;
  font-weight: bold;
}

.input-area {
  padding: 25px;
  background: rgba(0, 0, 0, 0.4);
  border-top: 2px solid rgba(139, 92, 246, 0.3);
  display: flex;
  gap: 12px;
}

.input-area input {
  flex: 1;
  padding: 14px 20px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 25px;
  font-size: 16px;
  color: white;
  outline: none;
  transition: all 0.3s;
}

.input-area input::placeholder {
  color: rgba(196, 181, 253, 0.5);
}

.input-area input:focus {
  border-color: #a78bfa;
  box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

.input-area button {
  padding: 14px 28px;
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  color: white;
  border: none;
  border-radius: 25px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
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
  background: #8b5cf6;
  border-radius: 4px;
}

.chat-area::-webkit-scrollbar-thumb:hover {
  background: #a78bfa;
}
</style>
