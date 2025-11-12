<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="logo-container">
          <svg class="logo-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:var(--color-primary);stop-opacity:1" />
                <stop offset="100%" style="stop-color:var(--color-primaryDark);stop-opacity:1" />
              </linearGradient>
            </defs>
            <circle cx="30" cy="50" r="25" fill="url(#logoGradient)" opacity="0.8"/>
            <circle cx="70" cy="50" r="25" fill="url(#logoGradient)" opacity="0.8"/>
            <path d="M 30 35 Q 50 20 70 35" stroke="var(--color-primaryLight)" stroke-width="4" fill="none" stroke-linecap="round"/>
            <circle cx="30" cy="45" r="4" fill="white"/>
            <circle cx="70" cy="45" r="4" fill="white"/>
            <path d="M 20 55 Q 30 65 40 55" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
            <path d="M 60 55 Q 70 65 80 55" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
          </svg>
          <h1>PingMe</h1>
        </div>
        <p>Zaloguj się do swojego konta</p>
      </div>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="login-type-section">
          <label class="login-type-label">Logowanie</label>
          <div class="login-type-buttons">
            <button
              type="button"
              class="login-type-btn"
              :class="{ active: loginType === 'username' }"
              @click="loginType = 'username'"
            >
              Login
            </button>
            <button
              type="button"
              class="login-type-btn"
              :class="{ active: loginType === 'email' }"
              @click="loginType = 'email'"
            >
              Mail
            </button>
          </div>
        </div>

        <div class="form-group" v-if="loginType === 'email'">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="Wprowadź swój email"
            :required="loginType === 'email'"
          />
        </div>

        <div class="form-group" v-if="loginType === 'username'">
          <label for="username">Nazwa użytkownika</label>
          <input
            id="username"
            v-model="username"
            type="text"
            placeholder="Wprowadź swoją nazwę użytkownika"
            :required="loginType === 'username'"
          />
        </div>

        <div class="form-group">
          <label for="password">Hasło</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Wprowadź hasło"
            required
          />
        </div>

        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <button type="submit" class="submit-btn" :disabled="loading">
          {{ loading ? 'Logowanie...' : 'Zaloguj się' }}
        </button>
      </form>

      <div class="auth-footer">
        <p>Nie masz konta? <router-link to="/register">Zarejestruj się</router-link></p>
      </div>
    </div>

    <footer class="page-footer">
      <div class="footer-content">
        <a href="mailto:kontakt@test.com" class="footer-link">
          <div class="icon-wrapper">
            <svg class="envelope-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
              <path d="M3 7L12 13L21 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="icon-letter">i</span>
          </div>
          <span>kontakt@test.com</span>
        </a>
        <a href="tel:123213321" class="footer-link">
          <svg class="phone-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>123-213-321</span>
        </a>
      </div>
    </footer>
  </div>
</template>

<script>
import { applyTheme, getTextSizeClass } from '../themes.js'

export default {
  name: 'Login',
  data() {
    return {
      email: '',
      username: '',
      password: '',
      error: '',
      loading: false,
      loginType: 'email',
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api'
    }
  },
  mounted() {
    // Apply saved theme
    const savedTheme = localStorage.getItem('theme') || 'purpleDream'
    applyTheme(savedTheme)

    // Apply saved text size
    const savedTextSize = localStorage.getItem('textSize') || 'medium'
    document.body.classList.add(getTextSizeClass(savedTextSize))
  },
  methods: {
    async handleLogin() {
      this.error = ''
      this.loading = true

      try {
        const loginData = {
          password: this.password
        }

        if (this.loginType === 'email') {
          loginData.email = this.email
        } else {
          loginData.username = this.username
        }

        const response = await fetch(`${this.apiUrl}/auth/login`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(loginData)
        })

        const data = await response.json()

        if (response.ok) {
          // Save JWT token
          localStorage.setItem('token', data.token)
          localStorage.setItem('user', JSON.stringify(data.user))
          // Save IP and login time for session validation
          localStorage.setItem('userIp', data.ip)
          localStorage.setItem('loginTime', new Date().toISOString())
          localStorage.setItem('lastActivity', new Date().toISOString())
          this.$router.push('/dashboard')
        } else {
          this.error = data.error || 'Błąd logowania'
        }
      } catch (error) {
        console.error('Login error:', error)
        this.error = 'Wystąpił błąd podczas logowania'
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.auth-container {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f1e 100%);
  padding: 20px 20px 0 20px;
}

.logo-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-bottom: 10px;
}

.logo-icon {
  width: 60px;
  height: 60px;
  filter: drop-shadow(0 0 20px rgba(167, 139, 250, 0.5));
  animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.auth-card {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(0, 0, 0, 0.8) 100%);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 20px;
  padding: 40px;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
  margin: auto 0;
}

.auth-header {
  text-align: center;
  margin-bottom: 30px;
}

.auth-header h1 {
  color: var(--color-primaryLight);
  font-size: 36px;
  margin-bottom: 10px;
  text-shadow: 0 0 20px rgba(167, 139, 250, 0.5);
}

.auth-header p {
  color: var(--color-text);
  font-size: 16px;
}

.auth-form {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  color: var(--color-text);
  font-size: 14px;
  margin-bottom: 8px;
  font-weight: 600;
}

.login-type-section {
  margin-bottom: 25px;
}

.login-type-label {
  display: block;
  color: var(--color-text);
  font-size: 16px;
  margin-bottom: 12px;
  font-weight: 600;
  text-align: center;
}

.login-type-buttons {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.login-type-btn {
  flex: 1;
  max-width: 150px;
  padding: 12px 24px;
  background: rgba(139, 92, 246, 0.1);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 12px;
  color: var(--color-text);
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.login-type-btn:hover {
  background: rgba(139, 92, 246, 0.2);
  border-color: rgba(139, 92, 246, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
}

.login-type-btn.active {
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  border-color: var(--color-primaryLight);
  color: white;
  box-shadow: 0 5px 20px rgba(139, 92, 246, 0.5);
}

.form-group input {
  width: 100%;
  padding: 14px 18px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid rgba(139, 92, 246, 0.3);
  border-radius: 12px;
  color: white;
  font-size: 16px;
  transition: all 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: var(--color-primaryLight);
  box-shadow: 0 0 20px rgba(167, 139, 250, 0.3);
}

.form-group input::placeholder {
  color: rgba(196, 181, 253, 0.5);
}

.error-message {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: #fca5a5;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 20px;
  text-align: center;
}

.submit-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.6);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  text-align: center;
  color: var(--color-text);
}

.auth-footer a {
  color: var(--color-primaryLight);
  text-decoration: none;
  font-weight: bold;
  transition: color 0.3s;
}

.auth-footer a:hover {
  color: var(--color-text);
  text-decoration: underline;
}

.page-footer {
  width: 100%;
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(0, 0, 0, 0.9) 100%);
  backdrop-filter: blur(10px);
  border-top: 2px solid rgba(139, 92, 246, 0.3);
  padding: 12px 20px;
  margin-top: 0;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
  flex-wrap: wrap;
}

.footer-link {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--color-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s;
  padding: 8px 16px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(139, 92, 246, 0.2);
}

.footer-link:hover {
  color: var(--color-primaryLight);
  background: rgba(139, 92, 246, 0.2);
  border-color: var(--color-primaryLight);
  transform: translateY(-2px);
  text-decoration: none;
}

.icon-wrapper {
  position: relative;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.envelope-icon {
  width: 24px;
  height: 24px;
  color: var(--color-primaryLight);
}

.icon-letter {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 10px;
  font-weight: bold;
  color: var(--color-primaryLight);
  margin-top: 1px;
}

.phone-icon {
  width: 20px;
  height: 20px;
  color: var(--color-primaryLight);
}

/* Responsive Design */
@media (max-width: 768px) {
  .auth-container {
    padding: 15px 15px 0 15px;
  }

  .auth-card {
    padding: 30px 20px;
    max-width: 100%;
  }

  .logo-container {
    gap: 12px;
  }

  .logo-icon {
    width: 50px;
    height: 50px;
  }

  .auth-header h1 {
    font-size: 28px;
  }

  .auth-header p {
    font-size: 15px;
  }

  .auth-header {
    margin-bottom: 25px;
  }

  .form-group {
    margin-bottom: 18px;
  }

  .form-group input {
    padding: 12px 16px;
    font-size: 15px;
  }

  .submit-btn {
    padding: 13px;
    font-size: 15px;
  }

  .page-footer {
    padding: 10px 15px;
  }

  .footer-content {
    flex-direction: column;
    gap: 12px;
  }

  .footer-link {
    width: 100%;
    justify-content: center;
    padding: 10px 14px;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 10px 10px 0 10px;
  }

  .auth-card {
    padding: 25px 18px;
    border-radius: 16px;
  }

  .logo-container {
    gap: 10px;
  }

  .logo-icon {
    width: 45px;
    height: 45px;
  }

  .auth-header h1 {
    font-size: 26px;
  }

  .auth-header p {
    font-size: 14px;
  }

  .auth-header {
    margin-bottom: 20px;
  }

  .form-group {
    margin-bottom: 16px;
  }

  .form-group label {
    font-size: 13px;
    margin-bottom: 6px;
  }

  .form-group input {
    padding: 11px 14px;
    font-size: 14px;
  }

  .submit-btn {
    padding: 12px;
    font-size: 14px;
  }

  .auth-footer {
    font-size: 14px;
  }

  .page-footer {
    padding: 10px;
  }

  .footer-content {
    gap: 10px;
  }

  .footer-link {
    padding: 8px 12px;
    font-size: 13px;
  }

  .footer-link span {
    font-size: 12px;
  }

  .icon-wrapper {
    width: 20px;
    height: 20px;
  }

  .envelope-icon {
    width: 20px;
    height: 20px;
  }

  .phone-icon {
    width: 18px;
    height: 18px;
  }
}

@media (max-width: 375px) {
  .auth-container {
    padding: 8px 8px 0 8px;
  }

  .auth-card {
    padding: 20px 15px;
    border-radius: 14px;
  }

  .logo-icon {
    width: 40px;
    height: 40px;
  }

  .auth-header h1 {
    font-size: 24px;
  }

  .auth-header p {
    font-size: 13px;
  }

  .form-group input {
    padding: 10px 12px;
    font-size: 14px;
  }

  .submit-btn {
    padding: 11px;
    font-size: 14px;
  }

  .footer-link {
    padding: 8px 10px;
    font-size: 12px;
  }

  .footer-link span {
    font-size: 11px;
  }
}
</style>
