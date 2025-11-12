<template>
  <div class="message-list" ref="messageList">
    <MessageItem
      v-for="message in messages"
      :key="message.id"
      :message="message"
    />
  </div>
</template>

<script>
import MessageItem from './MessageItem.vue';

export default {
  name: 'MessageList',
  components: {
    MessageItem,
  },
  props: {
    messages: {
      type: Array,
      required: true,
    },
  },
  watch: {
    messages() {
      this.$nextTick(() => {
        const list = this.$refs.messageList;
        list.scrollTop = list.scrollHeight;
      });
    },
  },
};
</script>

<style scoped>
.message-list {
  flex-grow: 1;
  padding: 20px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Custom scrollbar for a more modern look */
.message-list::-webkit-scrollbar {
  width: 8px;
}

.message-list::-webkit-scrollbar-track {
  background: var(--primary-color);
}

.message-list::-webkit-scrollbar-thumb {
  background-color: var(--secondary-color);
  border-radius: 10px;
  border: 2px solid var(--primary-color);
}
</style>
