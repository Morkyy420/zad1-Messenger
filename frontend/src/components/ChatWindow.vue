<template>
  <div class="chat-window">
    <h2 class="chat-title">Gemini Chat</h2>
    <MessageList :messages="messages" />
    <MessageInput @send="sendMessage" />
  </div>
</template>

<script>
import MessageList from './MessageList.vue';
import MessageInput from './MessageInput.vue';

export default {
  name: 'ChatWindow',
  components: {
    MessageList,
    MessageInput,
  },
  data() {
    return {
      messages: [],
    };
  },
  methods: {
    async sendMessage(content) {
      try {
        const response = await fetch('/api/messages', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ content }),
        });
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        // The message will be added via the Mercure push, so we don't need to add it here.
      } catch (error) {
        console.error('Error sending message:', error);
      }
    },
    subscribeToMercure() {
      // The URL should match the one in your Mercure configuration
      const url = new URL('http://localhost:9090/.well-known/mercure');
      // Subscribe to updates for a specific conversation topic
      url.searchParams.append('topic', 'http://chat.com/conversation/1');

      const eventSource = new EventSource(url);

      eventSource.onmessage = (event) => {
        const newMessage = JSON.parse(event.data);
        this.messages.push(newMessage);
      };

      eventSource.onerror = (error) => {
        console.error('Mercure EventSource failed:', error);
        eventSource.close();
      };
    },
  },
  mounted() {
    // In a real app, you would fetch initial messages here.
    // For now, we'll just start with an empty list.
    this.subscribeToMercure();
  },
};
</script>

<style scoped>
.chat-window {
  width: 80vw;
  max-width: 800px;
  height: 90vh;
  max-height: 900px;
  background-color: var(--primary-color);
  border-radius: 15px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--secondary-color);
}

.chat-title {
  padding: 20px;
  margin: 0;
  background-color: var(--secondary-color);
  color: var(--text-color);
  font-size: 1.5em;
  border-bottom: 1px solid var(--primary-color);
  text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
}
</style>
