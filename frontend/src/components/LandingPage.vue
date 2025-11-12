<template>
  <div class="landing-page">
    <div class="hero-section">
      <div class="logo-container">
        <div class="logo-icon">💬</div>
        <h1 class="logo-title">PingMe</h1>
      </div>
      <p class="tagline">Nowoczesna platforma komunikacji dla przyjaciół</p>

      <div class="cta-buttons">
        <button @click="goToLogin" class="btn-primary">Zaloguj się</button>
        <button @click="goToRegister" class="btn-secondary">Zarejestruj się</button>
      </div>

      <div class="features-grid">
        <div class="feature-card">
          <span class="feature-icon">💬</span>
          <h3>Chat w czasie rzeczywistym</h3>
          <p>Rozmawiaj ze znajomymi natychmiast</p>
        </div>
        <div class="feature-card">
          <span class="feature-icon">🖼️</span>
          <h3>Galeria zdjęć</h3>
          <p>Dziel się wspomnieniami wizualnymi</p>
        </div>
        <div class="feature-card">
          <span class="feature-icon">📅</span>
          <h3>Wydarzenia</h3>
          <p>Planuj spotkania i przypomnienia</p>
        </div>
        <div class="feature-card">
          <span class="feature-icon">🎨</span>
          <h3>4 Motywy</h3>
          <p>Personalizuj wygląd aplikacji</p>
        </div>
      </div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
      <button @click="showInfo = !showInfo" class="info-toggle">
        {{ showInfo ? '▼ Ukryj informacje' : '▶ Informacje o projekcie' }}
      </button>

      <transition name="expand">
        <div v-if="showInfo" class="info-content">
          <div class="info-block">
            <h2>📋 Spis treści projektu</h2>
            <div class="toc">
              <div class="toc-item">
                <strong>🔐 Autoryzacja:</strong>
                <ul>
                  <li>System logowania i rejestracji</li>
                  <li>Przechowywanie sesji w localStorage</li>
                  <li>JWT token authentication</li>
                </ul>
              </div>
              <div class="toc-item">
                <strong>👥 Znajomi:</strong>
                <ul>
                  <li>Lista znajomych użytkownika</li>
                  <li>Wyszukiwanie i dodawanie nowych znajomych</li>
                  <li>Niestandardowe pseudonimy dla znajomych</li>
                </ul>
              </div>
              <div class="toc-item">
                <strong>💬 Chat:</strong>
                <ul>
                  <li>Wiadomości tekstowe w czasie rzeczywistym</li>
                  <li>Przesyłanie zdjęć i wideo</li>
                  <li>GIF picker z integracją Giphy</li>
                  <li>Reakcje emoji na wiadomości</li>
                  <li>Edycja i usuwanie wiadomości</li>
                  <li>Drag & drop plików</li>
                </ul>
              </div>
              <div class="toc-item">
                <strong>🖼️ Galeria:</strong>
                <ul>
                  <li>Upload zdjęć (JPG, PNG, GIF, WebP, BMP)</li>
                  <li>Upload wideo (MP4)</li>
                  <li>Podpisy do zdjęć</li>
                  <li>Usuwanie zdjęć</li>
                </ul>
              </div>
              <div class="toc-item">
                <strong>📅 Wydarzenia:</strong>
                <ul>
                  <li>Kalendarz z widokiem miesięcznym</li>
                  <li>Dodawanie wydarzeń z datą i godziną</li>
                  <li>Lista zadań (To-Do)</li>
                  <li>Oznaczanie zadań jako wykonane</li>
                </ul>
              </div>
              <div class="toc-item">
                <strong>⚙️ Ustawienia:</strong>
                <ul>
                  <li>4 motywy kolorystyczne</li>
                  <li>3 rozmiary tekstu</li>
                  <li>24 avatary emoji</li>
                  <li>7 kolorów tła avatara</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="info-block">
            <h2>🛠️ Wykorzystane technologie</h2>
            <div class="tech-grid">
              <div class="tech-category">
                <h3>Frontend:</h3>
                <ul>
                  <li><strong>Vue.js 3</strong> - Framework JavaScript</li>
                  <li><strong>Tailwind CSS</strong> - Utility-first CSS</li>
                  <li><strong>Vue Router</strong> - Routing SPA</li>
                  <li><strong>Axios/Fetch API</strong> - HTTP requests</li>
                  <li><strong>Giphy API</strong> - GIF picker</li>
                </ul>
              </div>
              <div class="tech-category">
                <h3>Backend:</h3>
                <ul>
                  <li><strong>Symfony 6</strong> - PHP Framework</li>
                  <li><strong>Doctrine ORM</strong> - Database abstraction</li>
                  <li><strong>MySQL/SQLite</strong> - Relational database</li>
                  <li><strong>API Platform</strong> - REST API</li>
                </ul>
              </div>
              <div class="tech-category">
                <h3>Narzędzia:</h3>
                <ul>
                  <li><strong>npm</strong> - Package manager</li>
                  <li><strong>Composer</strong> - PHP dependencies</li>
                  <li><strong>Git</strong> - Version control</li>
                  <li><strong>WSL2</strong> - Development environment</li>
                </ul>
              </div>
            </div>
          </div>

          <div class="info-block">
            <h2>📁 Struktura projektu</h2>
            <pre class="code-block">
my_first_project/
├── frontend/                    # Vue.js aplikacja
│   ├── src/
│   │   ├── components/         # Komponenty Vue
│   │   │   ├── Dashboard.vue   # Główny panel
│   │   │   ├── Friends.vue     # Lista znajomych
│   │   │   ├── ChatRoom.vue    # Okno czatu
│   │   │   ├── Gallery.vue     # Galeria zdjęć
│   │   │   ├── Events.vue      # Wydarzenia i zadania
│   │   │   ├── Settings.vue    # Ustawienia użytkownika
│   │   │   └── LandingPage.vue # Strona główna
│   │   ├── assets/
│   │   │   └── tailwind.css    # Style CSS + motywy
│   │   ├── themes.js           # Definicje motywów
│   │   ├── router.js           # Routing aplikacji
│   │   └── main.js             # Entry point
│   └── public/
│       └── uploads/            # Przesłane pliki
│
├── src/                        # Symfony backend
│   ├── Controller/             # API endpoints
│   │   ├── AuthController.php
│   │   ├── ConversationController.php
│   │   ├── EventController.php
│   │   ├── PhotoController.php
│   │   └── TodoController.php
│   ├── Entity/                 # Database models
│   │   ├── User.php
│   │   ├── Message.php
│   │   ├── Conversation.php
│   │   ├── Event.php
│   │   └── Photo.php
│   └── Repository/             # Database queries
│
└── public/
    └── dist/                   # Built Vue app
            </pre>
          </div>

          <div class="info-block">
            <h2>🎨 Dostępne motywy</h2>
            <div class="theme-showcase">
              <div class="theme-demo dark-modern">
                <div class="theme-name">Dark Modern 🌙</div>
                <div class="theme-desc">Nowoczesny ciemny motyw</div>
              </div>
              <div class="theme-demo light-modern">
                <div class="theme-name">Light Modern ☀️</div>
                <div class="theme-desc">Minimalistyczny jasny</div>
              </div>
              <div class="theme-demo feminine">
                <div class="theme-name">Feminine Garden 🌸</div>
                <div class="theme-desc">Delikatny różowy z kwiatkami</div>
              </div>
              <div class="theme-demo dark-feminine">
                <div class="theme-name">Dark Floral Night 🌺</div>
                <div class="theme-desc">Ciemny kobiecy z kwiatami</div>
              </div>
            </div>
          </div>

          <div class="info-block">
            <h2>📊 Funkcjonalności</h2>
            <div class="features-list">
              <div class="feature-item">✅ Rejestracja i logowanie użytkowników</div>
              <div class="feature-item">✅ Zarządzanie znajomymi</div>
              <div class="feature-item">✅ Chat z przesyłaniem plików</div>
              <div class="feature-item">✅ Reakcje emoji</div>
              <div class="feature-item">✅ GIF picker (Giphy)</div>
              <div class="feature-item">✅ Galeria zdjęć i wideo</div>
              <div class="feature-item">✅ Kalendarz wydarzeń</div>
              <div class="feature-item">✅ Lista zadań To-Do</div>
              <div class="feature-item">✅ 4 motywy kolorystyczne</div>
              <div class="feature-item">✅ Personalizacja avatara</div>
              <div class="feature-item">✅ Edycja i usuwanie wiadomości</div>
              <div class="feature-item">✅ Responsive design (mobile/desktop)</div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- Footer -->
    <footer class="landing-footer">
      <p>© 2025 PingMe. Wszelkie prawa zastrzeżone.</p>
      <p class="contact">Kontakt: <a href="mailto:kontakt@pingme.pl">kontakt@pingme.pl</a></p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'LandingPage',
  data() {
    return {
      showInfo: false
    }
  },
  methods: {
    goToLogin() {
      this.$router.push('/login')
    },
    goToRegister() {
      this.$router.push('/register')
    }
  }
}
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 40px 20px;
  overflow-y: auto;
}

.hero-section {
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
  padding: 60px 20px;
}

.logo-container {
  margin-bottom: 30px;
  animation: fadeInDown 0.8s ease-out;
}

.logo-icon {
  width: 100px;
  height: 100px;
  background: linear-gradient(to bottom right, #667eea, #764ba2);
  border: 3px solid #000000;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 50px;
  margin-bottom: 20px;
  animation: bounce 2s infinite;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}

.logo-title {
  font-size: 64px;
  font-weight: bold;
  color: white;
  margin: 0;
  padding: 10px 0;
  line-height: 1.2;
}

.tagline {
  font-size: 24px;
  margin-bottom: 40px;
  opacity: 0.9;
}

.cta-buttons {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-bottom: 80px;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  padding: 16px 40px;
  font-size: 18px;
  font-weight: bold;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 180px;
}

.btn-primary {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 20px rgba(255, 255, 255, 0.3);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 30px rgba(255, 255, 255, 0.4);
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid white;
  backdrop-filter: blur(10px);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 30px;
  max-width: 1000px;
  margin: 0 auto;
}

.feature-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 30px;
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.feature-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 15px;
}

.feature-card h3 {
  font-size: 20px;
  margin-bottom: 10px;
}

.feature-card p {
  opacity: 0.8;
  font-size: 14px;
}

/* Info Section */
.info-section {
  max-width: 1200px;
  margin: 60px auto 40px;
  padding: 0 20px;
}

.info-toggle {
  width: 100%;
  padding: 20px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: white;
  font-size: 18px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.info-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.02);
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.4s ease;
  max-height: 10000px;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  max-height: 0;
}

.info-content {
  margin-top: 30px;
  animation: fadeIn 0.5s ease-out;
}

.info-block {
  background: rgba(255, 255, 255, 0.95);
  color: #1a202c;
  border-radius: 16px;
  padding: 40px;
  margin-bottom: 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.info-block h2 {
  color: #667eea;
  margin-bottom: 20px;
  font-size: 28px;
}

.info-block h3 {
  color: #764ba2;
  margin-bottom: 12px;
  font-size: 20px;
}

.toc {
  display: grid;
  gap: 20px;
}

.toc-item {
  background: #f7fafc;
  padding: 20px;
  border-radius: 12px;
  border-left: 4px solid #667eea;
}

.toc-item strong {
  display: block;
  margin-bottom: 10px;
  color: #667eea;
  font-size: 18px;
}

.toc-item ul {
  list-style: disc;
  margin-left: 25px;
  color: #4a5568;
}

.toc-item li {
  margin: 8px 0;
}

.tech-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
}

.tech-category {
  background: #f7fafc;
  padding: 25px;
  border-radius: 12px;
}

.tech-category ul {
  list-style: none;
  padding: 0;
}

.tech-category li {
  margin: 12px 0;
  padding-left: 20px;
  position: relative;
}

.tech-category li::before {
  content: '▸';
  position: absolute;
  left: 0;
  color: #667eea;
  font-weight: bold;
}

.code-block {
  background: #1a202c;
  color: #e2e8f0;
  padding: 25px;
  border-radius: 12px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.6;
}

.theme-showcase {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.theme-demo {
  padding: 30px;
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s ease;
}

.theme-demo:hover {
  transform: scale(1.05);
}

.theme-demo.dark-modern {
  background: linear-gradient(135deg, #1e293b, #0f172a);
  color: #f1f5f9;
}

.theme-demo.light-modern {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  color: #1e293b;
}

.theme-demo.feminine {
  background: linear-gradient(135deg, #fce7f3, #fbcfe8);
  color: #831843;
}

.theme-demo.dark-feminine {
  background: linear-gradient(135deg, #581c87, #831843);
  color: #fce7f3;
}

.theme-name {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 8px;
}

.theme-desc {
  font-size: 14px;
  opacity: 0.8;
}

.features-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 12px;
}

.feature-item {
  background: #f7fafc;
  padding: 15px 20px;
  border-radius: 8px;
  border-left: 3px solid #48bb78;
  color: #2d3748;
}

/* Footer */
.landing-footer {
  text-align: center;
  padding: 30px 20px;
  margin-top: 60px;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 14px;
  opacity: 0.8;
}

.landing-footer a {
  color: white;
  text-decoration: underline;
}

.landing-footer a:hover {
  opacity: 0.8;
}

.contact {
  margin-top: 10px;
}

/* Animations */
@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .logo-title {
    font-size: 48px;
  }

  .tagline {
    font-size: 18px;
  }

  .info-block {
    padding: 25px;
  }

  .code-block {
    font-size: 11px;
  }
}
</style>
