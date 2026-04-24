import { ref } from 'vue'
import axios from 'axios'
import api from '@/services/api'

export function useUpload() {
  const progress  = ref(0)
  const uploading = ref(false)
  const error     = ref(null)

  async function uploadProof({ file, orderId, installmentId = null }) {
    uploading.value = true
    error.value     = null
    progress.value  = 0

    const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'application/pdf']

    try {
      const ext      = file.name.split('.').pop()
      const mimeType = file.type

      if (!ALLOWED_MIMES.includes(mimeType)) {
        error.value = 'Formato inválido. Use JPG, PNG ou PDF.'
        uploading.value = false
        throw new Error(error.value)
      }

      if (file.size > 5 * 1024 * 1024) {
        error.value = 'Arquivo muito grande. Tamanho máximo: 5MB.'
        uploading.value = false
        throw new Error(error.value)
      }

      const { data } = await api.post('/payment-proofs', {
        order_id:       orderId,
        installment_id: installmentId,
        nome_arquivo:   file.name,
        tamanho_bytes:  file.size,
        mime_type:      mimeType,
        extensao:       ext,
      })

      const { upload_url, proof_id } = data.data

      if (upload_url) {
        await axios.put(upload_url, file, {
          headers: { 'Content-Type': mimeType },
          onUploadProgress: (e) => {
            progress.value = Math.round((e.loaded * 100) / e.total)
          },
        })
      } else {
        const form = new FormData()
        form.append('file', file)
        await api.post(`/payment-proofs/${proof_id}/file`, form, {
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (e) => {
            progress.value = Math.round((e.loaded * 100) / e.total)
          },
        })
      }

      progress.value = 100
      return proof_id
    } catch (e) {
      error.value = e.response?.data?.message || 'Erro ao enviar arquivo'
      throw e
    } finally {
      uploading.value = false
    }
  }

  return { progress, uploading, error, uploadProof }
}
