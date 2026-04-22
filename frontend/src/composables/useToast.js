import { ref } from 'vue'

const toasts = ref([])
let id = 0

export function useToast() {
  function show(message, type = 'info', duration = 3000) {
    const toast = { id: ++id, message, type }
    toasts.value.push(toast)
    setTimeout(() => remove(toast.id), duration)
  }

  function remove(toastId) {
    toasts.value = toasts.value.filter((t) => t.id !== toastId)
  }

  return { toasts, show, remove }
}
