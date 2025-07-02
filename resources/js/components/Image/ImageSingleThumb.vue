<template>
    <el-upload
        class="avatar-uploader"
        :http-request="uploadFile"
        :on-remove="handleRemove"
        :on-preview="handlePreview"
        :auto-upload="true"
        :limit="1"
        :multiple="false"
        :show-file-list="false"
    >
        <img v-if="imageUrl" :src="imageUrl" class="avatar" />
        <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
    </el-upload>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import axios from 'axios'

const props = defineProps({
    initialImage: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update'])

const imageUrl = ref(props.initialImage)

watch(() => props.initialImage, (val) => {
    imageUrl.value = val
})

const headers = {
    Authorization: `Bearer ${localStorage.getItem('token') || ''}`
}

const uploadFile = async (options) => {
    const { file, onSuccess, onError } = options

    const formData = new FormData()
    formData.append('image', file)

    try {
        const response = await axios.post('/api/images', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                ...headers
            }
        })
        if (response.data && response.data.path) {
            imageUrl.value = response.data.path
            emit('update', response.data.path)
            onSuccess(response.data)
            ElMessage.success('Изображение загружено')
        } else {
            onError(new Error('Не удалось получить путь к изображению'))
            ElMessage.error('Ошибка загрузки')
        }
    } catch (error) {
        onError(error)
        ElMessage.error('Ошибка загрузки: ' + (error.response?.data?.message || error.message))
    }
}

const handleRemove = () => {
    imageUrl.value = ''
    emit('update', '')
    ElMessage.info('Изображение удалено')
}

const handlePreview = () => {
    if (imageUrl.value) {
        window.open(imageUrl.value, '_blank')
    }
}
</script>

<style scoped>
.avatar-uploader .avatar {
    width: 178px;
    height: 178px;
    display: block;
    object-fit: cover;
    border-radius: 6px;
}

.avatar-uploader .el-upload {
    border: 1px dashed var(--el-border-color);
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: var(--el-transition-duration-fast);
    width: 178px;
    height: 178px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-uploader .el-upload:hover {
    border-color: var(--el-color-primary);
}

.el-icon.avatar-uploader-icon {
    font-size: 48px;
    color: #8c939d;
    width: 178px;
    height: 178px;
    text-align: center;
    line-height: 178px;
}
</style>
