// Anti-debug and anti-inspection measures
// Note: These are basic protections and can be bypassed by determined attackers

export function initAntiDebug() {
  // Only enable in production
  if (process.env.NODE_ENV !== 'production') {
    return // Disable all protections in development
  }

  // Disable right-click
  document.addEventListener('contextmenu', (e) => {
    e.preventDefault()
    return false
  })

  // Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
  document.addEventListener('keydown', (e) => {
    if (
      e.keyCode === 123 || // F12
      (e.ctrlKey && e.shiftKey && e.keyCode === 73) || // Ctrl+Shift+I
      (e.ctrlKey && e.shiftKey && e.keyCode === 74) || // Ctrl+Shift+J
      (e.ctrlKey && e.keyCode === 85) // Ctrl+U
    ) {
      e.preventDefault()
      return false
    }
  })

  // Disable console methods in production
  const noop = () => {}
  console.log = noop
  console.warn = noop
  console.error = noop
  console.info = noop
  console.debug = noop
}

// Clear console periodically
export function clearConsolePeriodically() {
  // Only in production
  if (process.env.NODE_ENV !== 'production') {
    return
  }

  setInterval(() => {
    console.clear()
  }, 5000) // Less aggressive - every 5 seconds instead of 1
}
