<template>
  <div class="message-item" :class="{ 'sent-by-me': isSentByMe }">
    <div class="message-author">{{ message.author.username }}</div>
    <div class="message-content">
      <p>{{ message.content }}</p>
    </div>
    <div class="message-timestamp">{{ formatTimestamp(message.createdAt) }}</div>
  </div>
</template>

<script>
export default {
  name: 'MessageItem',
  props: {
    message: {
      type: Object,
      required: true,
    },
  },
  computed: {
    isSentByMe() {
      // In a real app, you'd compare with the current user's ID.
      // For now, we'll assume all messages from 'TestUser' are "sent by me".
      return this.message.author.username === 'TestUser';
    },
  },
  methods: {
    formatTimestamp(timestamp) {
      const date = new Date(timestamp);
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>

<style scoped>
.message-item {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  max-width: 70%;
  animation: fadeIn 0.5s ease-in-out;
}

.message-content {
  padding: 12px 18px;
  background-color: var(--secondary-color);
  border-radius: 20px;
  color: var(--text-color);
  word-wrap: break-word;
}

.message-author {
  font-size: 0.8em;
  color: #a0a0a0;
  margin-bottom: 5px;
  margin-left: 10px;
}

.message-timestamp {
  font-size: 0.7em;
  color: #a0a0a0;
  margin-top: 5px;
  margin-left: 10px;
}

/* Style for messages sent by the current user */
.message-item.sent-by-me {
  align-items: flex-end;
}

.message-item.sent-by-me .message-content {
  background-color: #4a90e2; /* A nice blue for sent messages */
}

.message-item.sent-by-me .message-author,
.message-item.sent-by-me .message-timestamp {
  margin-left: 0;
  margin-right: 10px;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
