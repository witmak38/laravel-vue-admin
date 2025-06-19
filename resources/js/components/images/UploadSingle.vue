<template>
    <!-- Включаем авто-загрузку, чтобы trigger срабатывал -->
    <!-- Добавляем отслеживание изменения для показа миниатюры -->
    <el-upload
        class="avatar-uploader"
        action=""
        :show-file-list="false"
        :auto-upload="true"
    @change="handleChange"
    :on-success="handleAvatarSuccess"
    >
    <!-- Показываем загруженную картинку, если есть -->
    <img v-if="imageUrl" :src="imageUrl" class="avatar" />
    <!-- Иконка "плюс" для загрузки, если картинки нет -->
    <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
    </el-upload>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import type { UploadFile, UploadChangeParam, UploadProps } from 'element-plus'

const imageUrl = ref('')

// При выборе файла сразу показываем миниатюру локально (даже до загрузки)
const handleChange = (info: UploadChangeParam) => {
    const file = info.file;
    if (file && file.raw) {
        imageUrl.value = URL.createObjectURL(file.raw);
    }
};

// Обработчик успешной загрузки (если сервер возвращает URL, его лучше здесь обновить)
const handleAvatarSuccess: UploadProps['onSuccess'] = (response, uploadFile) => {
    if (response && response.url) {
        imageUrl.value = response.url;
    } else if (uploadFile && uploadFile.raw) {
        imageUrl.value = URL.createObjectURL(uploadFile.raw);
    }
};

// Проверка перед загрузкой файла
const beforeAvatarUpload: UploadProps['beforeUpload'] = (rawFile) => {
    if (rawFile.type !== 'image/jpeg') {
        ElMessage.error('Avatar picture must be JPG format!')
        return false
    } else if (rawFile.size / 1024 / 1024 > 2) {
        ElMessage.error('Avatar picture size can not exceed 2MB!')
        return false
    }
    return true
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
</style>

<style>
.avatar-uploader .el-upload {
    border: 1px dashed var(--el-border-color);
    border-radius: 6px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: var(--el-transition-duration-fast);
}

.avatar-uploader .el-upload:hover {
    border-color: var(--el-color-primary);
}

.el-icon.avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 178px;
    height: 178px;
    line-height: 178px;
    text-align: center;
    user-select: none;
}
</style>
