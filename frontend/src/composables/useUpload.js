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

    try {
      const ext      = file.name.split('.').pop()
      const mimeType = file.type

      const { data } = await api.post('/payment-proofs', {
        order_id:       orderId,
        installment_id: installmentId,
        nome_arquivo:   file.name,
        tamanho_bytes:  file.size,
        mime_type:      mimeType,
        extensao:       ext,
      })

      const { upload_url, proof_id } = data.data

      await axios.put(upload_url, file, {
        headers: { 'Content-Type': mimeType },
        onUploadProgress: (e) => {
          progress.value = Math.round((e.loaded * 100) / e.total)
        },
      })

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
