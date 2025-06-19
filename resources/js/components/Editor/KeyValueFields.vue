<template>
    <div>
        <div
            v-for="(item, index) in fields"
            :key="index"
            style="display: flex; gap: 10px; margin-bottom: 10px;"
        >
            <el-input
                v-model="item.key"
                placeholder="Ключ"
                style="flex: 1"
                clearable
            />
            <el-input
                v-model="item.value"
                placeholder="Значение"
                style="flex: 2"
                clearable
            />
            <!-- Кнопка с иконкой удаления -->
            <el-button
                type="danger"
                @click="removeField(index)"

                size="small"
            >
                <el-icon><Delete /></el-icon>
            </el-button>
        </div>

        <el-button
            type="primary"
            icon="el-icon-plus"
            @click="addField"
            size="small"
            plain
        >
            Добавить поле
        </el-button>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import { Delete } from '@element-plus/icons-vue' // импорт иконки

defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['update:modelValue'])

const fields = reactive([])

watch(
    () => modelValue,
    (newVal) => {
        fields.splice(0, fields.length, ...newVal)
    },
    { immediate: true }
)

function addField() {
    fields.push({ key: '', value: '' })
    emit('update:modelValue', fields)
}

function removeField(index) {
    fields.splice(index, 1)
    emit('update:modelValue', fields)
}

watch(
    () => fields,
    (newVal) => {
        emit('update:modelValue', newVal)
    },
    {deep: true}
)
</script>
