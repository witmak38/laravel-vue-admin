<template>
    <div class="html-editor">
        <div ref="editorContainer"></div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, defineEmits, defineProps } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'  // стили редактора

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['update:modelValue'])

const editorContainer = ref(null)
let quillInstance = null

onMounted(() => {
    quillInstance = new Quill(editorContainer.value, {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered'}, { list: 'bullet' }],
                ['link', 'image', 'code-block'],
                ['clean']
            ]
        },
    })

    // Установить начальное содержимое
    quillInstance.root.innerHTML = props.modelValue

    // Следим за изменениями и эмитируем их наверх
    quillInstance.on('text-change', () => {
        emit('update:modelValue', quillInstance.root.innerHTML)
    })
})

// Если внешний value изменится — обновим редактор
watch(() => props.modelValue, (newVal) => {
    if (quillInstance && quillInstance.root.innerHTML !== newVal) {
        quillInstance.root.innerHTML = newVal || ''
    }
})

onBeforeUnmount(() => {
    quillInstance = null
})
</script>

<style scoped>
.html-editor {
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    min-height: 200px;
    width: 100%;
}
.html-editor .ql-container {
    min-height: 180px;
}
</style>
