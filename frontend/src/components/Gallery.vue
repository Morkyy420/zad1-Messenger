<template>
  <div class="gallery-container">
    <div class="gallery-header">
      <h1>🖼️ Galeria Zdjęć</h1>
      <button class="add-photo-btn" @click="showUploadModal = true">
        ➕ Dodaj zdjęcie
      </button>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="modal-overlay" @click="closeUploadModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Dodaj nowe zdjęcie</h2>
          <button class="close-btn" @click="closeUploadModal">×</button>
        </div>
        <div class="modal-body">
          <div class="upload-area" @drop="handleDrop" @dragover.prevent @dragleave.prevent>
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              @change="handleFileSelect"
              style="display: none"
            />
            <div v-if="!selectedFile" class="upload-placeholder" @click="$refs.fileInput.click()">
              <div class="upload-icon">📸</div>
              <p>Kliknij lub przeciągnij zdjęcie</p>
            </div>
            <div v-else class="upload-preview">
              <img :src="previewUrl" alt="Preview" />
              <button class="remove-btn" @click="removeFile">×</button>
            </div>
          </div>
          <div class="caption-input">
            <input
              v-model="newCaption"
              type="text"
              placeholder="Dodaj podpis (opcjonalne)"
              maxlength="255"
            />
          </div>
          <div class="modal-actions">
            <button class="cancel-btn" @click="closeUploadModal">Anuluj</button>
            <button class="upload-btn" @click="uploadPhoto" :disabled="!selectedFile || uploading">
              {{ uploading ? 'Przesyłanie...' : 'Prześlij' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="editingPhoto" class="modal-overlay" @click="closeEditModal">
      <div class="modal-content edit-modal" @click.stop>
        <div class="modal-header">
          <h2>Edytuj zdjęcie</h2>
          <button class="close-btn" @click="closeEditModal">×</button>
        </div>
        <div class="modal-body">
          <div class="edit-tools">
            <button
              :class="['tool-btn', { active: editMode === 'crop' }]"
              @click="editMode = 'crop'"
            >
              ✂️ Przytnij
            </button>
            <button
              :class="['tool-btn', { active: editMode === 'draw' }]"
              @click="editMode = 'draw'"
            >
              ✏️ Rysuj
            </button>
            <button
              :class="['tool-btn', { active: editMode === 'text' }]"
              @click="editMode = 'text'"
            >
              📝 Tekst
            </button>
            <button class="tool-btn" @click="resetEdit">
              🔄 Reset
            </button>
          </div>

          <div class="edit-canvas-container">
            <canvas
              ref="editCanvas"
              :style="{ cursor: canvasCursor }"
              @mousedown="handleCanvasMouseDown"
              @mousemove="handleCanvasMouseMove"
              @mouseup="handleCanvasMouseUp"
              @mouseleave="handleCanvasMouseLeave"
              @click="handleCanvasClick"
            ></canvas>
          </div>

          <div v-if="editMode === 'crop'" class="crop-options">
            <p class="instructions">Zaznacz obszar do przycięcia przeciągając myszką</p>
            <button @click="applyCrop" :disabled="!isCropping && cropEndX === 0">Zastosuj przycięcie</button>
          </div>

          <div v-if="editMode === 'draw'" class="draw-options">
            <label>Kolor:</label>
            <input v-model="drawColor" type="color" />
            <label>Grubość:</label>
            <input v-model="drawWidth" type="range" min="1" max="20" />
          </div>

          <div v-if="editMode === 'text'" class="text-options">
            <!-- Dodawanie nowego tekstu -->
            <div v-if="selectedTextIndex === null" class="text-add-section">
              <input v-model="textToAdd" type="text" placeholder="Wpisz tekst" @keyup.enter="prepareTextPlacement" />
              <label>Kolor:</label>
              <input v-model="textColor" type="color" />
              <label>Rozmiar:</label>
              <input v-model="textSize" type="range" min="10" max="100" />
              <span class="size-label">{{ textSize }}px</span>
              <button @click="prepareTextPlacement" :disabled="!textToAdd.trim()">Dodaj tekst</button>
              <p v-if="placingText" class="instructions">Kliknij na zdjęcie aby umieścić tekst</p>
            </div>

            <!-- Edycja wybranego tekstu -->
            <div v-else class="text-edit-section">
              <div class="text-edit-header">
                <h4>Edycja tekstu</h4>
                <button class="deselect-btn" @click="deselectText">✕ Zamknij</button>
              </div>
              <input
                v-model="textObjects[selectedTextIndex].text"
                type="text"
                placeholder="Treść tekstu"
                @input="redrawCanvas"
              />
              <div class="text-controls-row">
                <label>Kolor:</label>
                <input
                  v-model="textObjects[selectedTextIndex].color"
                  type="color"
                  @input="redrawCanvas"
                />
                <label>Rozmiar:</label>
                <input
                  v-model.number="textObjects[selectedTextIndex].size"
                  type="range"
                  min="10"
                  max="100"
                  @input="redrawCanvas"
                />
                <span class="size-label">{{ textObjects[selectedTextIndex].size }}px</span>
              </div>
              <div class="text-position-controls">
                <label>Pozycja X:</label>
                <input
                  v-model.number="textObjects[selectedTextIndex].x"
                  type="range"
                  :min="0"
                  :max="editCanvas ? editCanvas.width : 800"
                  @input="redrawCanvas"
                />
                <span class="position-label">{{ Math.round(textObjects[selectedTextIndex].x) }}</span>
              </div>
              <div class="text-position-controls">
                <label>Pozycja Y:</label>
                <input
                  v-model.number="textObjects[selectedTextIndex].y"
                  type="range"
                  :min="0"
                  :max="editCanvas ? editCanvas.height : 600"
                  @input="redrawCanvas"
                />
                <span class="position-label">{{ Math.round(textObjects[selectedTextIndex].y) }}</span>
              </div>
              <button class="delete-text-btn" @click="deleteSelectedText">🗑️ Usuń ten tekst</button>
            </div>

            <!-- Lista tekstów -->
            <div v-if="textObjects.length > 0" class="text-list">
              <p class="text-list-header">Teksty na zdjęciu ({{ textObjects.length }}):</p>
              <div class="text-items">
                <div
                  v-for="(textObj, index) in textObjects"
                  :key="index"
                  :class="['text-item', { selected: selectedTextIndex === index }]"
                  @click="selectText(index)"
                >
                  <span class="text-preview">{{ textObj.text }}</span>
                  <span class="text-info">{{ textObj.size }}px</span>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-actions">
            <button class="cancel-btn" @click="closeEditModal">Anuluj</button>
            <button class="save-btn" @click="saveEditedPhoto">Zapisz</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Photos Grid -->
    <div v-if="loading" class="loading">Ładowanie zdjęć...</div>
    <div v-else-if="photos.length === 0" class="empty-state">
      <div class="empty-icon">📷</div>
      <p>Brak zdjęć w galerii</p>
      <button class="add-first-btn" @click="showUploadModal = true">Dodaj pierwsze zdjęcie</button>
    </div>
    <div v-else class="photos-grid">
      <div v-for="photo in photos" :key="photo.id" class="photo-card">
        <div class="photo-wrapper" @click="openPhotoView(photo)">
          <img :src="photo.url" :alt="photo.caption || 'Photo'" />
        </div>
        <div class="photo-info">
          <div v-if="editingCaptionId === photo.id" class="caption-edit">
            <input
              v-model="editedCaption"
              type="text"
              @keyup.enter="saveCaption(photo.id)"
              @blur="saveCaption(photo.id)"
              ref="captionInput"
            />
          </div>
          <div v-else class="caption-display" @click="startEditCaption(photo)">
            <p class="caption">{{ photo.caption || 'Kliknij aby dodać podpis' }}</p>
          </div>
          <p class="upload-date">{{ formatDate(photo.uploadedAt) }}</p>
        </div>
        <div class="photo-actions">
          <button class="action-btn edit" @click="startEdit(photo)" title="Edytuj">✏️</button>
          <button class="action-btn delete" @click="deletePhoto(photo.id)" title="Usuń">🗑️</button>
        </div>
      </div>
    </div>

    <!-- Photo View Modal -->
    <div v-if="viewingPhoto" class="modal-overlay" @click="viewingPhoto = null">
      <div class="photo-view-modal" @click.stop>
        <button class="close-btn" @click="viewingPhoto = null">×</button>
        <img :src="viewingPhoto.url" :alt="viewingPhoto.caption" />
        <div class="photo-view-info">
          <p v-if="viewingPhoto.caption" class="caption">{{ viewingPhoto.caption }}</p>
          <p class="date">{{ formatDate(viewingPhoto.uploadedAt) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Gallery',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      photos: [],
      loading: false,
      showUploadModal: false,
      selectedFile: null,
      previewUrl: null,
      newCaption: '',
      uploading: false,
      editingPhoto: null,
      editMode: 'crop',
      drawColor: '#ff0000',
      drawWidth: 5,
      textColor: '#ffffff',
      textSize: 30,
      textToAdd: '',
      isDrawing: false,
      lastX: 0,
      lastY: 0,
      editingCaptionId: null,
      editedCaption: '',
      viewingPhoto: null,
      apiUrl: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
      originalImageData: null,
      editCanvas: null,
      editCtx: null,
      // Crop variables
      isCropping: false,
      cropStartX: 0,
      cropStartY: 0,
      cropEndX: 0,
      cropEndY: 0,
      // Text placement variables
      placingText: false,
      textX: 0,
      textY: 0,
      // Text objects array
      textObjects: [],
      selectedTextIndex: null,
      isDraggingText: false,
      dragOffsetX: 0,
      dragOffsetY: 0
    }
  },
  computed: {
    canvasCursor() {
      if (this.editMode === 'crop') return 'crosshair'
      if (this.editMode === 'draw') return 'crosshair'
      if (this.editMode === 'text' && this.placingText) return 'text'
      return 'default'
    }
  },
  mounted() {
    this.loadPhotos()
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token')
      if (!token) {
        console.error('[Gallery] No token found - forcing logout')
        localStorage.clear()
        this.$router.push('/login')
        return {}
      }
      return {
        'Authorization': `Bearer ${token}`
      }
    },
    async loadPhotos() {
      this.loading = true
      try {
        const response = await fetch(`${this.apiUrl}/photos/${this.currentUser.id}`, {
          headers: this.getAuthHeaders()
        })
        const data = await response.json()
        this.photos = Array.isArray(data) ? data : []
      } catch (error) {
        console.error('Error loading photos:', error)
        alert('Błąd podczas ładowania zdjęć')
      } finally {
        this.loading = false
      }
    },
    handleFileSelect(event) {
      const file = event.target.files[0]
      if (file) {
        this.processFile(file)
      }
    },
    handleDrop(event) {
      event.preventDefault()
      const file = event.dataTransfer.files[0]
      if (file && file.type.startsWith('image/')) {
        this.processFile(file)
      }
    },
    processFile(file) {
      // Check file type - images and videos
      const validTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo'
      ]
      if (!validTypes.includes(file.type.toLowerCase())) {
        alert(`Nieprawidłowy typ pliku: ${file.type}. Dozwolone formaty: JPEG, JPG, PNG, GIF, WebP, BMP, MP4`)
        console.error('Invalid file type:', file.type)
        return
      }

      // Larger size limit for videos (50MB)
      const maxSize = file.type.startsWith('video/') ? 50 * 1024 * 1024 : 10 * 1024 * 1024
      if (file.size > maxSize) {
        const limit = file.type.startsWith('video/') ? '50MB' : '10MB'
        alert(`Plik jest za duży (max ${limit})`)
        return
      }

      console.log('File accepted:', file.name, file.type, file.size)
      this.selectedFile = file
      this.previewUrl = URL.createObjectURL(file)
    },
    removeFile() {
      this.selectedFile = null
      this.previewUrl = null
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },
    async uploadPhoto() {
      if (!this.selectedFile) return

      this.uploading = true
      console.log('Uploading file:', this.selectedFile.name, this.selectedFile.type)

      const formData = new FormData()
      formData.append('file', this.selectedFile)
      formData.append('userId', this.currentUser.id)
      formData.append('caption', this.newCaption)

      try {
        const response = await fetch(`${this.apiUrl}/photos`, {
          method: 'POST',
          headers: this.getAuthHeaders(),
          body: formData
        })

        if (response.ok) {
          await this.loadPhotos()
          this.closeUploadModal()
          alert('Zdjęcie dodane pomyślnie!')
        } else {
          const error = await response.json()
          console.error('Upload failed:', error)
          let errorMsg = 'Błąd: ' + (error.error || 'Nie udało się przesłać zdjęcia')
          if (error.receivedMimeType) {
            errorMsg += `\n\nTyp pliku: ${error.receivedMimeType}`
            errorMsg += `\nAkceptowane typy: JPEG, PNG, GIF, WebP, BMP`
          }
          alert(errorMsg)
        }
      } catch (error) {
        console.error('Upload error:', error)
        alert('Błąd podczas przesyłania zdjęcia: ' + error.message)
      } finally {
        this.uploading = false
      }
    },
    closeUploadModal() {
      this.showUploadModal = false
      this.removeFile()
      this.newCaption = ''
    },
    startEdit(photo) {
      this.editingPhoto = photo
      this.editMode = 'crop'
      this.$nextTick(() => {
        this.initEditCanvas(photo.url)
      })
    },
    initEditCanvas(imageUrl) {
      const canvas = this.$refs.editCanvas
      if (!canvas) {
        console.error('Canvas not found')
        return
      }

      this.editCanvas = canvas
      this.editCtx = canvas.getContext('2d')

      const img = new Image()

      img.onerror = (error) => {
        console.error('Error loading image:', error)
        alert('Błąd podczas ładowania zdjęcia. Spróbuj ponownie.')
      }

      img.onload = () => {
        const maxWidth = 800
        const maxHeight = 600
        let width = img.width
        let height = img.height

        if (width > maxWidth) {
          height = (height * maxWidth) / width
          width = maxWidth
        }
        if (height > maxHeight) {
          width = (width * maxHeight) / height
          height = maxHeight
        }

        canvas.width = width
        canvas.height = height
        this.editCtx.drawImage(img, 0, 0, width, height)
        this.originalImageData = this.editCtx.getImageData(0, 0, width, height)
        console.log('Canvas initialized successfully', width, height)
      }

      // Always use crossOrigin for API images (localhost:8080 vs localhost:8000 are different origins)
      img.crossOrigin = 'anonymous'
      img.src = imageUrl
    },
    handleCanvasMouseDown(e) {
      const rect = this.editCanvas.getBoundingClientRect()
      const x = e.clientX - rect.left
      const y = e.clientY - rect.top

      if (this.editMode === 'crop') {
        this.isCropping = true
        this.cropStartX = x
        this.cropStartY = y
        this.cropEndX = x
        this.cropEndY = y
      } else if (this.editMode === 'draw') {
        this.isDrawing = true
        this.lastX = x
        this.lastY = y
      } else if (this.editMode === 'text') {
        // Check if mousedown on text for dragging
        const clickedTextIndex = this.findTextAtPosition(x, y)
        if (clickedTextIndex !== -1) {
          this.isDraggingText = true
          this.selectedTextIndex = clickedTextIndex
          const textObj = this.textObjects[clickedTextIndex]
          this.dragOffsetX = x - textObj.x
          this.dragOffsetY = y - textObj.y
        }
      }
    },
    handleCanvasMouseMove(e) {
      const rect = this.editCanvas.getBoundingClientRect()
      const x = e.clientX - rect.left
      const y = e.clientY - rect.top

      if (this.editMode === 'crop' && this.isCropping) {
        this.cropEndX = x
        this.cropEndY = y
        this.redrawWithCropSelection()
      } else if (this.editMode === 'draw' && this.isDrawing) {
        this.editCtx.beginPath()
        this.editCtx.moveTo(this.lastX, this.lastY)
        this.editCtx.lineTo(x, y)
        this.editCtx.strokeStyle = this.drawColor
        this.editCtx.lineWidth = this.drawWidth
        this.editCtx.lineCap = 'round'
        this.editCtx.stroke()
        this.lastX = x
        this.lastY = y
      } else if (this.editMode === 'text' && this.isDraggingText && this.selectedTextIndex !== null) {
        // Drag selected text
        this.textObjects[this.selectedTextIndex].x = x - this.dragOffsetX
        this.textObjects[this.selectedTextIndex].y = y - this.dragOffsetY
        this.redrawCanvas()
      }
    },
    handleCanvasMouseUp() {
      if (this.editMode === 'crop') {
        this.isCropping = false
      } else if (this.editMode === 'draw') {
        this.isDrawing = false
      } else if (this.editMode === 'text') {
        this.isDraggingText = false
      }
    },
    handleCanvasMouseLeave() {
      this.isCropping = false
      this.isDrawing = false
      this.isDraggingText = false
    },
    handleCanvasClick(e) {
      const rect = this.editCanvas.getBoundingClientRect()
      const x = e.clientX - rect.left
      const y = e.clientY - rect.top

      if (this.editMode === 'text' && this.placingText) {
        // Add new text object
        this.textObjects.push({
          text: this.textToAdd,
          x: x,
          y: y,
          color: this.textColor,
          size: this.textSize
        })

        this.placingText = false
        this.textToAdd = ''
        this.redrawCanvas()
      } else if (this.editMode === 'text' && !this.isDraggingText) {
        // Check if clicked on existing text
        this.checkTextClick(x, y)
      }
    },
    redrawWithCropSelection() {
      // Redraw original image
      if (this.originalImageData) {
        this.editCtx.putImageData(this.originalImageData, 0, 0)
      }

      // Draw crop selection rectangle
      const x = Math.min(this.cropStartX, this.cropEndX)
      const y = Math.min(this.cropStartY, this.cropEndY)
      const width = Math.abs(this.cropEndX - this.cropStartX)
      const height = Math.abs(this.cropEndY - this.cropStartY)

      this.editCtx.strokeStyle = '#00ff00'
      this.editCtx.lineWidth = 2
      this.editCtx.setLineDash([5, 5])
      this.editCtx.strokeRect(x, y, width, height)
      this.editCtx.setLineDash([])
    },
    applyCrop() {
      if (this.cropEndX === 0 || this.cropStartX === this.cropEndX || this.cropStartY === this.cropEndY) {
        alert('Zaznacz obszar do przycięcia')
        return
      }

      const x = Math.min(this.cropStartX, this.cropEndX)
      const y = Math.min(this.cropStartY, this.cropEndY)
      const width = Math.abs(this.cropEndX - this.cropStartX)
      const height = Math.abs(this.cropEndY - this.cropStartY)

      // Get cropped image data
      const croppedImageData = this.editCtx.getImageData(x, y, width, height)

      // Resize canvas to cropped size
      this.editCanvas.width = width
      this.editCanvas.height = height

      // Put cropped image
      this.editCtx.putImageData(croppedImageData, 0, 0)

      // Update original image data
      this.originalImageData = this.editCtx.getImageData(0, 0, width, height)

      // Reset crop selection
      this.cropStartX = 0
      this.cropStartY = 0
      this.cropEndX = 0
      this.cropEndY = 0
    },
    prepareTextPlacement() {
      if (!this.textToAdd.trim()) return
      this.placingText = true
      this.selectedTextIndex = null
    },
    redrawCanvas() {
      if (!this.editCtx || !this.originalImageData) return

      // Redraw original image
      this.editCtx.putImageData(this.originalImageData, 0, 0)

      // Redraw all text objects
      this.textObjects.forEach((textObj, index) => {
        this.editCtx.font = `${textObj.size}px Arial`
        this.editCtx.fillStyle = textObj.color

        // Draw selection box for selected text
        if (index === this.selectedTextIndex) {
          const metrics = this.editCtx.measureText(textObj.text)
          const textHeight = textObj.size
          this.editCtx.strokeStyle = '#00ff00'
          this.editCtx.lineWidth = 2
          this.editCtx.setLineDash([5, 5])
          this.editCtx.strokeRect(
            textObj.x - 5,
            textObj.y - textHeight - 5,
            metrics.width + 10,
            textHeight + 10
          )
          this.editCtx.setLineDash([])
        }

        this.editCtx.fillText(textObj.text, textObj.x, textObj.y)
      })
    },
    findTextAtPosition(x, y) {
      // Check from last to first (top text layer first)
      for (let i = this.textObjects.length - 1; i >= 0; i--) {
        const textObj = this.textObjects[i]
        this.editCtx.font = `${textObj.size}px Arial`
        const metrics = this.editCtx.measureText(textObj.text)
        const textHeight = textObj.size

        // Check if click is within text bounding box
        if (
          x >= textObj.x - 5 &&
          x <= textObj.x + metrics.width + 5 &&
          y >= textObj.y - textHeight - 5 &&
          y <= textObj.y + 5
        ) {
          return i
        }
      }
      return -1
    },
    checkTextClick(x, y) {
      const clickedTextIndex = this.findTextAtPosition(x, y)
      if (clickedTextIndex !== -1) {
        this.selectText(clickedTextIndex)
      } else {
        this.deselectText()
      }
    },
    selectText(index) {
      this.selectedTextIndex = index
      this.placingText = false
      this.redrawCanvas()
    },
    deselectText() {
      this.selectedTextIndex = null
      this.redrawCanvas()
    },
    deleteSelectedText() {
      if (this.selectedTextIndex !== null) {
        this.textObjects.splice(this.selectedTextIndex, 1)
        this.selectedTextIndex = null
        this.redrawCanvas()
      }
    },
    resetEdit() {
      if (this.originalImageData) {
        this.editCtx.putImageData(this.originalImageData, 0, 0)
      }
      // Reset crop and text state
      this.cropStartX = 0
      this.cropStartY = 0
      this.cropEndX = 0
      this.cropEndY = 0
      this.placingText = false
      this.textObjects = []
      this.selectedTextIndex = null
    },
    async saveEditedPhoto() {
      if (!this.editCanvas) return

      // Render final version without selection boxes
      if (this.originalImageData) {
        this.editCtx.putImageData(this.originalImageData, 0, 0)
      }

      // Render all text objects
      this.textObjects.forEach((textObj) => {
        this.editCtx.font = `${textObj.size}px Arial`
        this.editCtx.fillStyle = textObj.color
        this.editCtx.fillText(textObj.text, textObj.x, textObj.y)
      })

      this.editCanvas.toBlob(async (blob) => {
        const formData = new FormData()
        formData.append('file', blob, 'edited.jpg')
        formData.append('userId', this.currentUser.id)
        formData.append('caption', this.editingPhoto.caption || '')

        try {
          // Upload as new photo
          const response = await fetch(`${this.apiUrl}/photos`, {
            method: 'POST',
            headers: this.getAuthHeaders(),
            body: formData
          })

          if (response.ok) {
            await this.loadPhotos()
            this.closeEditModal()
            alert('Zdjęcie zapisane!')
          }
        } catch (error) {
          console.error('Save error:', error)
          alert('Błąd podczas zapisywania')
        }
      }, 'image/jpeg', 0.9)
    },
    closeEditModal() {
      this.editingPhoto = null
      this.editMode = 'crop'
      // Reset crop and text state
      this.cropStartX = 0
      this.cropStartY = 0
      this.cropEndX = 0
      this.cropEndY = 0
      this.placingText = false
      this.textToAdd = ''
      this.textObjects = []
      this.selectedTextIndex = null
    },
    startEditCaption(photo) {
      this.editingCaptionId = photo.id
      this.editedCaption = photo.caption || ''
      this.$nextTick(() => {
        if (this.$refs.captionInput && this.$refs.captionInput[0]) {
          this.$refs.captionInput[0].focus()
        }
      })
    },
    async saveCaption(photoId) {
      try {
        const authHeaders = this.getAuthHeaders()
        const response = await fetch(`${this.apiUrl}/photos/${photoId}`, {
          method: 'PATCH',
          headers: { ...authHeaders, 'Content-Type': 'application/json' },
          body: JSON.stringify({ caption: this.editedCaption })
        })

        if (response.ok) {
          const photo = this.photos.find(p => p.id === photoId)
          if (photo) {
            photo.caption = this.editedCaption
          }
        }
      } catch (error) {
        console.error('Error saving caption:', error)
      } finally {
        this.editingCaptionId = null
        this.editedCaption = ''
      }
    },
    async deletePhoto(photoId) {
      if (!confirm('Czy na pewno chcesz usunąć to zdjęcie?')) return

      try {
        const response = await fetch(`${this.apiUrl}/photos/${photoId}`, {
          method: 'DELETE',
          headers: this.getAuthHeaders()
        })

        if (response.ok) {
          this.photos = this.photos.filter(p => p.id !== photoId)
          alert('Zdjęcie usunięte')
        }
      } catch (error) {
        console.error('Error deleting photo:', error)
        alert('Błąd podczas usuwania zdjęcia')
      }
    },
    openPhotoView(photo) {
      this.viewingPhoto = photo
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('pl-PL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>

<style scoped>
.gallery-container {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.gallery-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.gallery-header h1 {
  color: var(--color-text);
  font-size: 32px;
  margin: 0;
}

.add-photo-btn {
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primaryDark));
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.add-photo-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s;
}

.modal-content {
  background: var(--color-cardBg);
  border-radius: 20px;
  padding: 30px;
  max-width: 600px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  border: 2px solid var(--color-border);
}

.modal-content.edit-modal {
  max-width: 900px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h2 {
  color: var(--color-text);
  margin: 0;
  font-size: 24px;
}

.close-btn {
  background: none;
  border: none;
  color: var(--color-text);
  font-size: 32px;
  cursor: pointer;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: rotate(90deg);
}

.upload-area {
  border: 3px dashed var(--color-border);
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s;
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area:hover {
  border-color: var(--color-primaryLight);
  background: rgba(139, 92, 246, 0.05);
}

.upload-placeholder {
  width: 100%;
}

.upload-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.upload-placeholder p {
  color: var(--color-textLight);
  font-size: 18px;
}

.upload-preview {
  position: relative;
  width: 100%;
}

.upload-preview img {
  max-width: 100%;
  max-height: 400px;
  border-radius: 12px;
}

.remove-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(239, 68, 68, 0.9);
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.caption-input {
  margin-top: 20px;
}

.caption-input input {
  width: 100%;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  color: var(--color-text);
  font-size: 16px;
  outline: none;
  transition: all 0.3s;
}

.caption-input input:focus {
  border-color: var(--color-primaryLight);
  box-shadow: 0 0 10px rgba(139, 92, 246, 0.2);
}

.modal-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.cancel-btn,
.upload-btn,
.save-btn {
  flex: 1;
  padding: 12px;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.cancel-btn {
  background: rgba(255, 255, 255, 0.1);
  color: var(--color-text);
}

.cancel-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.upload-btn,
.save-btn {
  background: linear-gradient(135deg, var(--color-primary), var(--color-primaryDark));
  color: white;
}

.upload-btn:hover,
.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
}

.upload-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.loading,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--color-textLight);
}

.empty-icon {
  font-size: 80px;
  margin-bottom: 20px;
}

.add-first-btn {
  margin-top: 20px;
  padding: 12px 24px;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
}

.photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.photo-card {
  background: var(--color-cardBg);
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid var(--color-border);
  transition: all 0.3s;
}

.photo-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(139, 92, 246, 0.2);
}

.photo-wrapper {
  cursor: pointer;
  aspect-ratio: 1;
  overflow: hidden;
}

.photo-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s;
}

.photo-wrapper:hover img {
  transform: scale(1.05);
}

.photo-info {
  padding: 16px;
}

.caption-display {
  cursor: pointer;
  min-height: 24px;
}

.caption {
  color: var(--color-text);
  font-size: 16px;
  margin: 0 0 8px 0;
  word-wrap: break-word;
}

.caption-edit input {
  width: 100%;
  padding: 8px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-primaryLight);
  border-radius: 8px;
  color: var(--color-text);
  font-size: 16px;
  outline: none;
}

.upload-date {
  color: var(--color-textLight);
  font-size: 14px;
  margin: 0;
}

.photo-actions {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: rgba(0, 0, 0, 0.2);
}

.action-btn {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 8px;
  font-size: 18px;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.edit {
  background: rgba(59, 130, 246, 0.2);
}

.action-btn.edit:hover {
  background: rgba(59, 130, 246, 0.4);
  transform: scale(1.05);
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.2);
}

.action-btn.delete:hover {
  background: rgba(239, 68, 68, 0.4);
  transform: scale(1.05);
}

.edit-tools {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.tool-btn {
  padding: 10px 16px;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid var(--color-border);
  border-radius: 12px;
  color: var(--color-text);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.tool-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.tool-btn.active {
  background: var(--color-primary);
  border-color: var(--color-primaryLight);
  color: white;
}

.edit-canvas-container {
  background: #000;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  justify-content: center;
  margin-bottom: 20px;
}

canvas {
  max-width: 100%;
  min-width: 400px;
  min-height: 300px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: crosshair;
  background: #1a1a1a;
}

.draw-options,
.text-options,
.crop-options {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 16px;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.draw-options label,
.text-options label {
  color: var(--color-text);
  font-weight: bold;
}

.draw-options input[type="color"],
.text-options input[type="color"] {
  width: 50px;
  height: 36px;
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
}

.draw-options input[type="range"],
.text-options input[type="range"] {
  flex: 1;
  min-width: 100px;
}

.text-options input[type="text"] {
  flex: 1;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.3);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  color: var(--color-text);
  outline: none;
}

.text-options button {
  padding: 8px 16px;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.text-options button:hover {
  background: var(--color-primaryLight);
}

.crop-options button {
  padding: 8px 16px;
  background: var(--color-primary);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.crop-options button:hover:not(:disabled) {
  background: var(--color-primaryLight);
}

.crop-options button:disabled,
.text-options button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.instructions {
  color: var(--color-textLight);
  font-size: 14px;
  font-style: italic;
  margin: 0;
  flex: 1 1 100%;
}

.text-add-section,
.text-edit-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.text-edit-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.text-edit-header h4 {
  margin: 0;
  color: var(--color-primary);
  font-size: 18px;
}

.deselect-btn {
  padding: 4px 12px;
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 6px;
  color: var(--color-text);
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.deselect-btn:hover {
  background: rgba(239, 68, 68, 0.4);
}

.text-controls-row,
.text-position-controls {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}

.size-label,
.position-label {
  min-width: 40px;
  text-align: right;
  color: var(--color-textLight);
  font-size: 14px;
  font-weight: bold;
}

.delete-text-btn {
  padding: 8px 16px;
  background: rgba(239, 68, 68, 0.3);
  color: white;
  border: 1px solid rgba(239, 68, 68, 0.5);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 14px;
}

.delete-text-btn:hover {
  background: rgba(239, 68, 68, 0.5);
}

.text-list {
  width: 100%;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 2px solid var(--color-border);
}

.text-list-header {
  color: var(--color-textLight);
  font-size: 14px;
  margin: 0 0 8px 0;
  font-weight: bold;
}

.text-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.text-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: rgba(0, 0, 0, 0.2);
  border: 2px solid var(--color-border);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.text-item:hover {
  background: rgba(0, 0, 0, 0.4);
  border-color: var(--color-primaryLight);
}

.text-item.selected {
  background: rgba(139, 92, 246, 0.2);
  border-color: var(--color-primary);
}

.text-preview {
  color: var(--color-text);
  font-size: 14px;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.text-info {
  color: var(--color-textLight);
  font-size: 12px;
  margin-left: 8px;
}

.photo-view-modal {
  position: relative;
  max-width: 90%;
  max-height: 90%;
  border-radius: 12px;
  overflow: hidden;
}

.photo-view-modal img {
  max-width: 100%;
  max-height: calc(90vh - 100px);
  display: block;
}

.photo-view-modal .close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(0, 0, 0, 0.7);
  z-index: 10;
}

.photo-view-info {
  background: rgba(0, 0, 0, 0.8);
  padding: 20px;
  text-align: center;
}

.photo-view-info .caption {
  color: white;
  font-size: 18px;
  margin-bottom: 10px;
}

.photo-view-info .date {
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
  margin: 0;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 768px) {
  .photos-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
  }

  .gallery-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }

  .modal-content {
    padding: 20px;
  }
}
</style>
