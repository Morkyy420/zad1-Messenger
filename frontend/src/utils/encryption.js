import CryptoJS from 'crypto-js'

// Secret key for encryption (in production, this should be per-conversation and stored securely)
const SECRET_KEY = process.env.VUE_APP_ENCRYPTION_KEY || 'pingme-secret-key-2025'

export function encryptMessage(message) {
  if (!message) return ''
  try {
    return CryptoJS.AES.encrypt(message, SECRET_KEY).toString()
  } catch (error) {
    console.error('Encryption error:', error)
    return message
  }
}

export function decryptMessage(encryptedMessage) {
  if (!encryptedMessage) return ''
  try {
    const bytes = CryptoJS.AES.decrypt(encryptedMessage, SECRET_KEY)
    const decrypted = bytes.toString(CryptoJS.enc.Utf8)
    return decrypted || encryptedMessage
  } catch (error) {
    console.error('Decryption error:', error)
    return encryptedMessage
  }
}

// Generate a unique encryption key for a conversation
export function generateConversationKey(conversationId, userId1, userId2) {
  const sortedIds = [userId1, userId2].sort().join('-')
  return CryptoJS.SHA256(`${conversationId}-${sortedIds}-${SECRET_KEY}`).toString()
}

// Encrypt message with conversation-specific key
export function encryptMessageForConversation(message, conversationKey) {
  if (!message) return ''
  try {
    return CryptoJS.AES.encrypt(message, conversationKey).toString()
  } catch (error) {
    console.error('Encryption error:', error)
    return message
  }
}

// Decrypt message with conversation-specific key
export function decryptMessageForConversation(encryptedMessage, conversationKey) {
  if (!encryptedMessage) return ''

  // Check if message looks encrypted (AES encrypted messages are base64-like)
  // If it doesn't look encrypted, return as-is (old unencrypted message)
  if (!encryptedMessage.includes('U2FsdGVk') && encryptedMessage.length < 100 && !encryptedMessage.match(/^[A-Za-z0-9+/=]+$/)) {
    // Looks like plain text, not encrypted
    return encryptedMessage
  }

  try {
    const bytes = CryptoJS.AES.decrypt(encryptedMessage, conversationKey)
    const decrypted = bytes.toString(CryptoJS.enc.Utf8)

    // If decryption resulted in empty string or malformed data, return original
    if (!decrypted || decrypted.length === 0) {
      return encryptedMessage
    }

    return decrypted
  } catch (error) {
    // Decryption failed - probably an old unencrypted message
    return encryptedMessage
  }
}
