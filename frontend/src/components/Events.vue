<template>
  <div class="events-container">
    <div class="events-panel">
      <div class="panel-header">
        <h2>📅 Wydarzenia</h2>
        <button class="add-event-btn" @click="showAddEventForm = !showAddEventForm">
          {{ showAddEventForm ? '❌ Anuluj' : '➕ Dodaj wydarzenie' }}
        </button>
      </div>

      <!-- Success notification -->
      <div v-if="successMessage" class="success-notification">
        {{ successMessage }}
      </div>

      <!-- Formularz dodawania wydarzenia -->
      <div v-if="showAddEventForm" class="add-event-form">
        <input v-model="newEvent.title" placeholder="Tytuł wydarzenia" />
        <textarea v-model="newEvent.description" placeholder="Opis (opcjonalnie)"></textarea>
        <div class="form-row">
          <input v-model="newEvent.date" type="date" />
          <input v-model="newEvent.time" type="time" />
        </div>
        <button @click="addEvent" class="save-btn">Zapisz wydarzenie</button>
      </div>

      <div class="events-content">
        <!-- Kalendarz -->
        <div class="calendar-section">
          <div class="calendar-header">
            <button @click="previousMonth" class="nav-btn">❮</button>
            <h3>{{ currentMonthYear }}</h3>
            <button @click="nextMonth" class="nav-btn">❯</button>
          </div>

          <div class="calendar-grid">
            <div v-for="day in daysOfWeek" :key="day" class="calendar-day-name">{{ day }}</div>
            <div
              v-for="(day, index) in calendarDays"
              :key="index"
              :class="['calendar-day', {
                'other-month': day.otherMonth,
                'today': day.isToday,
                'selected': day.date === selectedDate,
                'has-events': dayHasEvents(day.date)
              }]"
              @click="selectDate(day.date)"
            >
              <span class="day-number">{{ day.day }}</span>
              <span v-if="dayHasEvents(day.date)" class="event-dot"></span>
            </div>
          </div>
        </div>

        <!-- Wydarzenia na wybrany dzień -->
        <div class="events-list-section">
          <h3>Wydarzenia - {{ selectedDateFormatted }}</h3>
          <div v-if="selectedDayEvents.length === 0" class="empty-state">
            Brak wydarzeń na ten dzień
          </div>
          <div v-else class="events-list">
            <div v-for="event in selectedDayEvents" :key="event.id" class="event-card">
              <div class="event-header">
                <h4>{{ event.title }}</h4>
                <span class="event-time">{{ event.time }}</span>
              </div>
              <p v-if="event.description" class="event-description">{{ event.description }}</p>
              <div class="event-meta">
                <span class="event-author">{{ event.author.username }}</span>
                <button @click="deleteEvent(event.id)" class="delete-btn">🗑️</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista rzeczy do zrobienia -->
        <div class="todo-section">
          <div class="todo-header">
            <h3>📝 Rzeczy do zrobienia</h3>
            <button @click="showAddTodoForm = !showAddTodoForm" class="add-todo-btn">+</button>
          </div>

          <div v-if="showAddTodoForm" class="add-todo-form">
            <input
              v-model="newTodo"
              placeholder="Nowe zadanie..."
              @keyup.enter="addTodo"
            />
            <button @click="addTodo">Dodaj</button>
          </div>

          <div class="todo-list">
            <div v-for="todo in todos" :key="todo.id" class="todo-item">
              <input
                type="checkbox"
                :checked="todo.completed"
                @change="toggleTodo(todo.id)"
              />
              <span :class="{ completed: todo.completed }">{{ todo.text }}</span>
              <button @click="deleteTodo(todo.id)" class="delete-btn-small">×</button>
            </div>
            <div v-if="todos.length === 0" class="empty-state-small">
              Brak zadań
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Events',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      currentDate: new Date(),
      selectedDate: new Date().toISOString().split('T')[0],
      events: [],
      todos: [],
      showAddEventForm: false,
      showAddTodoForm: false,
      newEvent: {
        title: '',
        description: '',
        date: new Date().toISOString().split('T')[0],
        time: '12:00'
      },
      newTodo: '',
      daysOfWeek: ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'Sb', 'Nd'],
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
      successMessage: ''
    }
  },
  computed: {
    currentMonthYear() {
      return this.currentDate.toLocaleDateString('pl-PL', { month: 'long', year: 'numeric' })
    },
    selectedDateFormatted() {
      // Parse date as YYYY-MM-DD without timezone conversion
      const [year, month, day] = this.selectedDate.split('-').map(Number)
      const date = new Date(year, month - 1, day)
      return date.toLocaleDateString('pl-PL', { day: 'numeric', month: 'long', year: 'numeric' })
    },
    calendarDays() {
      const year = this.currentDate.getFullYear()
      const month = this.currentDate.getMonth()

      const firstDay = new Date(year, month, 1)
      const lastDay = new Date(year, month + 1, 0)

      // Polski tydzień zaczyna się od poniedziałku (1), a nie niedzieli (0)
      let startDay = firstDay.getDay() - 1
      if (startDay === -1) startDay = 6

      const days = []

      // Helper function to format date as YYYY-MM-DD without timezone issues
      const formatDate = (year, month, day) => {
        const y = year.toString()
        const m = (month + 1).toString().padStart(2, '0')
        const d = day.toString().padStart(2, '0')
        return `${y}-${m}-${d}`
      }

      // Poprzedni miesiąc
      const prevMonthLastDay = new Date(year, month, 0).getDate()
      for (let i = startDay - 1; i >= 0; i--) {
        const day = prevMonthLastDay - i
        days.push({
          day,
          date: formatDate(year, month - 1, day),
          otherMonth: true,
          isToday: false
        })
      }

      // Aktualny miesiąc
      const now = new Date()
      const today = formatDate(now.getFullYear(), now.getMonth(), now.getDate())

      for (let day = 1; day <= lastDay.getDate(); day++) {
        const dateStr = formatDate(year, month, day)
        days.push({
          day,
          date: dateStr,
          otherMonth: false,
          isToday: dateStr === today
        })
      }

      // Następny miesiąc
      const remaining = 42 - days.length // 6 tygodni x 7 dni
      for (let day = 1; day <= remaining; day++) {
        days.push({
          day,
          date: formatDate(year, month + 1, day),
          otherMonth: true,
          isToday: false
        })
      }

      return days
    },
    selectedDayEvents() {
      return this.events.filter(event => event.date === this.selectedDate)
    }
  },
  mounted() {
    this.loadEvents()
    this.loadTodos()
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      if (!token) {
        console.error('[Events] No token found - forcing logout')
        localStorage.clear()
        this.$router.push('/login')
        return {}
      }
      return {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    },
    previousMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1)
    },
    nextMonth() {
      this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1)
    },
    selectDate(date) {
      this.selectedDate = date
      // Auto-ustaw datę w formularzu nowego wydarzenia
      this.newEvent.date = date
      // NIE otwieraj formularza - tylko pokaż wydarzenia z tego dnia
    },
    dayHasEvents(date) {
      return this.events.some(event => event.date === date)
    },
    async loadEvents() {
      try {
        const response = await fetch(`${this.apiUrl}/events`, {
          headers: this.getAuthHeaders()
        })
        if (response.ok) {
          this.events = await response.json()
        }
      } catch (error) {
        console.error('Error loading events:', error)
      }
    },
    async addEvent() {
      if (!this.newEvent.title.trim()) {
        alert('Tytuł wydarzenia jest wymagany')
        return
      }

      if (!this.currentUser || !this.currentUser.id) {
        alert('Błąd: Brak zalogowanego użytkownika')
        console.error('currentUser:', this.currentUser)
        return
      }

      try {
        const response = await fetch(`${this.apiUrl}/events`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            ...this.newEvent,
            userId: this.currentUser.id
          })
        })

        if (response.ok) {
          await this.loadEvents()
          this.newEvent = {
            title: '',
            description: '',
            date: new Date().toISOString().split('T')[0],
            time: '12:00'
          }
          this.showAddEventForm = false
          this.successMessage = '✓ Wydarzenie dodane pomyślnie!'
          setTimeout(() => {
            this.successMessage = ''
          }, 3000)
        } else {
          const error = await response.json()
          alert('Błąd podczas dodawania wydarzenia: ' + (error.error || 'Nieznany błąd'))
          console.error('Response error:', error)
        }
      } catch (error) {
        console.error('Error adding event:', error)
        alert('Błąd połączenia z serwerem')
      }
    },
    async deleteEvent(eventId) {
      try {
        const response = await fetch(`${this.apiUrl}/events/${eventId}`, {
          method: 'DELETE',
          headers: this.getAuthHeaders()
        })

        if (response.ok) {
          await this.loadEvents()
        }
      } catch (error) {
        console.error('Error deleting event:', error)
      }
    },
    async loadTodos() {
      try {
        const response = await fetch(`${this.apiUrl}/todos/${this.currentUser.id}`, {
          headers: this.getAuthHeaders()
        })
        if (response.ok) {
          this.todos = await response.json()
        }
      } catch (error) {
        console.error('Error loading todos:', error)
      }
    },
    async addTodo() {
      if (!this.newTodo.trim()) {
        alert('Treść zadania jest wymagana')
        return
      }

      if (!this.currentUser || !this.currentUser.id) {
        alert('Błąd: Brak zalogowanego użytkownika')
        return
      }

      try {
        const response = await fetch(`${this.apiUrl}/todos`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            text: this.newTodo,
            userId: this.currentUser.id
          })
        })

        if (response.ok) {
          await this.loadTodos()
          this.newTodo = ''
          this.showAddTodoForm = false
        } else {
          const error = await response.json()
          alert('Błąd podczas dodawania zadania: ' + (error.error || 'Nieznany błąd'))
        }
      } catch (error) {
        console.error('Error adding todo:', error)
        alert('Błąd połączenia z serwerem')
      }
    },
    async toggleTodo(todoId) {
      try {
        const todo = this.todos.find(t => t.id === todoId)
        const response = await fetch(`${this.apiUrl}/todos/${todoId}`, {
          method: 'PATCH',
          headers: this.getAuthHeaders(),
          body: JSON.stringify({
            completed: !todo.completed
          })
        })

        if (response.ok) {
          await this.loadTodos()
        }
      } catch (error) {
        console.error('Error toggling todo:', error)
      }
    },
    async deleteTodo(todoId) {
      try {
        const response = await fetch(`${this.apiUrl}/todos/${todoId}`, {
          method: 'DELETE',
          headers: this.getAuthHeaders()
        })

        if (response.ok) {
          await this.loadTodos()
        }
      } catch (error) {
        console.error('Error deleting todo:', error)
      }
    }
  }
}
</script>

<style scoped>
.events-container {
  width: 100%;
  max-width: 1400px;
  height: 90vh;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 0 auto;
}

.events-panel {
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

.add-event-btn {
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

.add-event-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px var(--color-primary);
}

.add-event-form {
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-border);
  border-radius: 15px;
  padding: 20px;
  margin-bottom: 20px;
}

.add-event-form input,
.add-event-form textarea {
  width: 100%;
  padding: 12px;
  margin-bottom: 12px;
  background: rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.add-event-form textarea {
  min-height: 80px;
  resize: vertical;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-row input {
  flex: 1;
}

.save-btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primaryDark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.save-btn:hover {
  transform: scale(1.02);
}

.events-content {
  flex: 1;
  display: grid;
  grid-template-columns: 1.5fr 1fr 0.8fr;
  gap: 20px;
  overflow: hidden;
}

.calendar-section,
.events-list-section,
.todo-section {
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-border);
  border-radius: 15px;
  padding: 20px;
  overflow-y: auto;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-header h3 {
  color: var(--color-text);
  margin: 0;
  font-size: 18px;
  text-transform: capitalize;
}

.nav-btn {
  background: var(--color-primary);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-btn:hover {
  background: var(--color-primaryLight);
  transform: scale(1.1);
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 5px;
}

.calendar-day-name {
  text-align: center;
  color: var(--color-textLight);
  font-size: 12px;
  font-weight: bold;
  padding: 8px 0;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.calendar-day:hover {
  background: var(--color-primary);
  transform: scale(1.05);
}

.calendar-day.other-month {
  opacity: 0.3;
}

.calendar-day.today {
  border: 2px solid var(--color-primaryLight);
  background: var(--color-primary);
}

.calendar-day.selected {
  background: var(--color-primaryDark);
  box-shadow: 0 0 10px var(--color-primary);
}

.calendar-day.has-events .day-number {
  font-weight: bold;
  color: var(--color-primaryLighter);
}

.day-number {
  color: var(--color-text);
  font-size: 14px;
}

.event-dot {
  width: 4px;
  height: 4px;
  background: var(--color-primaryLight);
  border-radius: 50%;
  position: absolute;
  bottom: 4px;
}

.events-list-section h3,
.todo-header h3 {
  color: var(--color-text);
  font-size: 16px;
  margin-bottom: 15px;
}

.events-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.event-card {
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid var(--color-border);
  border-radius: 10px;
  padding: 15px;
  transition: all 0.2s;
}

.event-card:hover {
  border-color: var(--color-primaryLight);
  transform: translateX(5px);
}

.event-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.event-header h4 {
  color: var(--color-primaryLight);
  margin: 0;
  font-size: 16px;
}

.event-time {
  color: var(--color-textLight);
  font-size: 14px;
}

.event-description {
  color: var(--color-text);
  font-size: 14px;
  margin: 8px 0;
}

.event-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--color-border);
}

.event-author {
  color: var(--color-textLight);
  font-size: 12px;
}

.delete-btn {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  padding: 4px 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.delete-btn:hover {
  background: rgba(239, 68, 68, 0.4);
  transform: scale(1.1);
}

.todo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.add-todo-btn {
  width: 32px;
  height: 32px;
  background: var(--color-primary);
  border: none;
  color: white;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.2s;
}

.add-todo-btn:hover {
  background: var(--color-primaryLight);
  transform: scale(1.1);
}

.add-todo-form {
  display: flex;
  gap: 8px;
  margin-bottom: 15px;
}

.add-todo-form input {
  flex: 1;
  padding: 10px;
  background: rgba(0, 0, 0, 0.5);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  color: white;
  font-size: 14px;
}

.add-todo-form button {
  padding: 10px 16px;
  background: var(--color-primary);
  border: none;
  color: white;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}

.add-todo-form button:hover {
  background: var(--color-primaryLight);
}

.todo-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.todo-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  transition: all 0.2s;
}

.todo-item:hover {
  border-color: var(--color-primaryLight);
}

.todo-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.todo-item span {
  flex: 1;
  color: var(--color-text);
  font-size: 14px;
}

.todo-item span.completed {
  text-decoration: line-through;
  opacity: 0.5;
}

.delete-btn-small {
  background: none;
  border: none;
  color: var(--color-textLight);
  font-size: 20px;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-btn-small:hover {
  color: #fca5a5;
  transform: scale(1.2);
}

.empty-state {
  text-align: center;
  color: var(--color-textLight);
  padding: 40px 20px;
  font-style: italic;
}

.empty-state-small {
  text-align: center;
  color: var(--color-textLight);
  padding: 20px;
  font-size: 14px;
  font-style: italic;
}

/* Success notification */
.success-notification {
  background: rgba(34, 197, 94, 0.2);
  border: 2px solid rgba(34, 197, 94, 0.5);
  color: #86efac;
  padding: 15px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 600;
  text-align: center;
  animation: slideDown 0.3s ease-out;
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

/* Responsive */
@media (max-width: 1200px) {
  .events-content {
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
  }
}

@media (max-width: 768px) {
  .events-panel {
    padding: 20px;
  }

  .panel-header {
    flex-direction: column;
    gap: 15px;
    align-items: stretch;
  }

  .add-event-btn {
    width: 100%;
  }

  .calendar-day {
    font-size: 12px;
  }

  .day-number {
    font-size: 12px;
  }
}
</style>
