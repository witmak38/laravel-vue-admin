<template>
    <el-upload
        class="upload-demo"
        action=""
        list-type="picture-card"
        :file-list="fileList"
        :http-request="uploadFile"
        :on-remove="handleRemove"
        :on-preview="handlePreview"
        :auto-upload="true"
        :headers="headers"
        multiple
    >
        <el-icon><Plus /></el-icon>
    </el-upload>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import ImageResource from '@/api/image' // твой API класс
import { Plus } from '@element-plus/icons-vue'

const props = defineProps({
    modelId: { type: [Number, String], required: true },
    modelType: { type: String, required: true },
})

const emit = defineEmits(['update'])

const imageApi = new ImageResource()

const fileList = ref([])
const headers = {} // если нужна авторизация — добавь сюда Authorization

// Загружаем уже сохранённые картинки при монтировании
const fetchImages = async () => {
    try {
        const res = await imageApi.index({
            imageable_type: props.modelType,
            imageable_id: props.modelId
        })
        fileList.value = res.data.map(img => ({
            name: img.title || 'image',
            url: img.path,
            uid: img.id,
            status: 'done',
            response: img
        }))
    } catch (err) {
        console.error(err)
    }
}

// Загружаем новую картинку
const uploadFile = async (options) => {
    const formData = new FormData()
    formData.append('image', options.file)
    formData.append('imageable_type', props.modelType)
    formData.append('imageable_id', props.modelId)

    try {
        const response = await imageApi.upload(formData)
        fileList.value.push({
            name: response.image.title || 'image',
            url: response.image.path,
            uid: response.image.id,
            status: 'done',
            response: response.image
        })
        emit('update', fileList.value.map(f => f.response))
        ElMessage.success('Изображение загружено')
        options.onSuccess(response.image)
    } catch (err) {
        console.error(err)
        ElMessage.error('Ошибка загрузки')
        options.onError(err)
    }
}

// Удаляем картинку
const handleRemove = async (file) => {
    try {
        await ElMessageBox.confirm('Удалить изображение?', 'Подтверждение', {
            type: 'warning'
        })
        const id = file.uid
        await imageApi.destroy(id)
        fileList.value = fileList.value.filter(f => f.uid !== id)
        emit('update', fileList.value.map(f => f.response))
        ElMessage.success('Удалено')
    } catch (err) {
        if (err !== 'cancel') ElMessage.error('Ошибка удаления')
    }
}

// При клике на картинку открывать в новом окне
const handlePreview = (file) => {
    window.open(file.url, '_blank')
}

onMounted(() => {
    fetchImages()
})
</script>

<style scoped>
.upload-demo {
    margin-top: 10px;
}
</style>
