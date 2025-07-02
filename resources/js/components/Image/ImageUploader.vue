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
        :limit="limit"
        multiple
    >
        <!-- :show-file-list="false" :disabled="isDisabled" если потребуется скрыть загруженные картинки -->
        <!-- Просто слот для плюса, без клика -->
        <template #trigger>
            <div style="cursor: pointer;">
                <el-icon><Plus /></el-icon>
            </div>
        </template>
    </el-upload>
    <el-image-viewer v-if="showPreview" :url-list="[previewUrl]" @close="showPreview = false" />
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox, ElImageViewer } from 'element-plus'
import ImageResource from '@/api/image' // твой API класс
import { Plus } from '@element-plus/icons-vue'

const props = defineProps({
    modelId: { type: [Number, String], required: true },
    modelType: { type: String, required: true },
    initialImages: { type: Array, default: () => [] },
})

const emit = defineEmits(['update'])

const imageApi = new ImageResource()

const fileList = ref([])
const headers = {} // если нужна авторизация — добавь сюда Authorization

const showPreview = ref(false)
const previewUrl = ref('')

const isDisabled = computed(() => fileList.value.length >= limit)

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

    if (fileList.value.length >= props.limit) {
        ElMessage.warning(`Достигнут лимит загрузки: максимум ${props.limit} изображений.`)
        options.onError(new Error('Limit reached'))
        return
    }
    const formData = new FormData()
    formData.append('image', options.file)
    formData.append('imageable_type', props.modelType)
    formData.append('imageable_id', props.modelId)

    // ВАЖНО: Проверь что реально ушло
    for (const [key, value] of formData.entries()) {
        console.log(key, value)
    }
    try {
        const response = await imageApi.upload(formData)
        console.log('upload response:', response)
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
// const handlePreview = (file) => {
//     window.open(file.url, '_blank')
// }
const handlePreview = (file) => {
    if (file.url) {
        previewUrl.value = file.url
        showPreview.value = true
    }
}

// При клике на плюсик — открыть первую картинку (если есть), иначе показать сообщение
// const handleAddClick = () => {
//     if (fileList.value.length > 0) {
//         const firstFile = fileList.value[0]
//         if (firstFile.url) {
//             previewUrl.value = firstFile.url
//             showPreview.value = true
//         }
//     } else {
//         ElMessage.info('Сначала загрузите изображение')
//     }
// }

// onMounted(() => {
//     fetchImages()
// })

// Watch for changes in initialImages prop to update fileList
watch(
    () => props.initialImages,
    (newImages) => {
        fileList.value = (newImages || []).map(img => ({
            name: img.title || 'image',
            url: img.url || img.path,
            uid: img.id,
            status: 'done',
            response: img
        }))
        console.log('Final normalizedImages:', fileList.value)
    },
    { immediate: true }
)
</script>

<style scoped>
.upload-demo {
    margin-top: 10px;
}
</style>
