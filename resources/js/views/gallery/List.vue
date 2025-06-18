<template>
    <div class="app-container">
        <el-upload
            class="upload-demo"
            action=""
            :http-request="uploadFile"
            list-type="picture-card"
            :file-list="fileList"
            :on-remove="handleRemove"
            :on-preview="handlePreview"
            :headers="headers"
            multiple
            :auto-upload="true"
        >
            <el-icon><Plus /></el-icon>
        </el-upload>

        <el-image-viewer
            v-if="previewVisible"
            :url-list="[previewUrl]"
            @close="previewVisible = false"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import GalleryResource from '@/api/gallery'

const gallery = new GalleryResource()

const fileList = ref([])
const previewVisible = ref(false)
const previewUrl = ref('')
const headers = {
    // Добавь авторизацию, если нужно
    // Authorization: `Bearer ${localStorage.getItem('token')}`
}

const fetchPhotos = async () => {
    const { data } = await gallery.list({ per_page: 100 })
    fileList.value = data.map(item => ({
        name: item.id,
        url: `/storage/${item.path}`
    }))
}

const uploadFile = async (options) => {
    const formData = new FormData()
    formData.append('image', options.file)

    try {
        await gallery.upload(formData)
        ElMessage.success('Фото загружено')
        fetchPhotos()
    } catch (err) {
        console.error(err)
        ElMessage.error('Ошибка загрузки')
    }
}

const handleRemove = async (file) => {
    try {
        await gallery.destroy(file.name)
        ElMessage.success('Фото удалено')
        fetchPhotos()
    } catch (err) {
        console.error(err)
        ElMessage.error('Ошибка удаления')
    }
}

const handlePreview = (file) => {
    previewUrl.value = file.url
    previewVisible.value = true
}

onMounted(fetchPhotos)
</script>

<style scoped>
.upload-demo {
    margin-bottom: 20px;
}
</style>
