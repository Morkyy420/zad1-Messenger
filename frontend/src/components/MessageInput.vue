<template>
  <form class="message-input-form" @submit.prevent="onSubmit">
    <input
      type="text"
      class="message-input"
      v-model="content"
      placeholder="Type a message..."
    />
    <button type="submit" class="send-button">Send</button>
  </form>
</template>

<script>
export default {
  name: 'MessageInput',
  data() {
    return {
      content: '',
    };
  },
  methods: {
    onSubmit() {
      if (this.content.trim()) {
        this.$emit('send', this.content);
        this.content = '';
      }
    },
  },
};
</script>

<style scoped>
.message-input-form {
  display: flex;
  padding: 20px;
  background-color: var(--secondary-color);
  border-top: 1px solid var(--primary-color);
}

.message-input {
  flex-grow: 1;
  padding: 12px 15px;
  border: none;
  border-radius: 20px;
  background-color: var(--background-color);
  color: var(--text-color);
  font-size: 1em;
  outline: none;
  transition: background-color 0.3s;
}

.message-input:focus {
  background-color: #2a2a4e; /* Slightly lighter for focus */
}

.send-button {
  margin-left: 15px;
  padding: 12px 25px;
  border: none;
  border-radius: 20px;
  background-color: #4a90e2;
  color: white;
  font-size: 1em;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.send-button:hover {
  background-color: #3a80d2;
}
</style>
