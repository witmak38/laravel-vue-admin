<template>
    <div class="app-container scroll-y">
        <div class="filter-container">
            <!-- Кнопка добавления новой страницы -->
            <el-button class="filter-item" type="primary" :icon="Plus" @click="handleCreate">
                {{ t('table.add') }}
            </el-button>
        </div>

        <!-- Компонент таблицы со списком страниц -->
        <custom-table
            :table-data="tableData"
            :table-column="tableColumns"
            :table-option="tableOption"
            :pagination="pagination"
            :paginate="true"
            :page-sizes="pageSizes"
            :loading="loading"
            @table-action="tableActions"
            @set-params="setParams"
        />

        <!-- Диалог создания новой страницы -->
        <el-dialog :title="t('page.create')" v-model="dialogFormVisible" width="80%">
            <div v-loading="creating" class="form-container">
                <el-form ref="refPageForm" :model="newPage" :rules="rules" label-position="left" label-width="120px">
                    <el-tabs v-model="activeTab" tab-position="left" style="min-height: 400px;">
                        <!-- Первый таб со всей формой -->
                        <el-tab-pane label="Основные" name="main">
                            <el-container>
                                <el-header>
                                    <el-form-item :label="t('page.name')" prop="name">
                                        <el-input v-model="newPage.name" />
                                    </el-form-item>
                                </el-header>
                                <el-container>
                                    <el-aside width="200px">
                                        <el-form-item label-position="top" :label="t('page.image')">
                                            <upload-single
                                                v-model:file-url="newPage.image"
                                                :limit="1"
                                                :on-exceed="handleExceed"
                                                class="custom-upload"
                                                list-type="picture-card"
                                            />
                                        </el-form-item>
                                    </el-aside>
                                    <el-container>
                                        <el-main>
                                            <el-form-item :label="t('page.title')" prop="title">
                                                <el-input v-model="newPage.title" />
                                            </el-form-item>
                                            <el-form-item :label="t('page.description')" prop="description">
                                                <el-input v-model="newPage.description" />
                                            </el-form-item>
                                            <el-form-item :label="t('page.keywords')" prop="keywords">
                                                <el-input v-model="newPage.keywords" />
                                            </el-form-item>
                                        </el-main>
                                    </el-container>
                                </el-container>
                            </el-container>

                            <el-container>
                                <el-main>
                                    <el-form-item :label="t('page.slug')" prop="slug">
                                        <el-input v-model="newPage.slug" />
                                    </el-form-item>
                                    <el-form-item :label="t('page.content')">
                                        <html-editor v-model:content="newPage.content" />
                                    </el-form-item>

                                </el-main>
                            </el-container>
                        </el-tab-pane>

                        <el-tab-pane label="Дополнительные поля" name="custom">
                            <!-- Можно добавить что-то позже -->
                            <el-container>
                                <el-main>
                                    <el-form-item label="Дополнительные поля" label-position="top" label-width="150px">
                                        <KeyValueFields v-model="form.customFields" />
                                    </el-form-item>
                                </el-main>
                            </el-container>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>

                <div class="dialog-footer" style="margin-top: 20px;">
                    <el-button @click="dialogFormVisible = false">
                        {{ t('table.cancel') }}
                    </el-button>
                    <el-button type="primary" @click="createPage(refPageForm)">
                        {{ t('table.confirm') }}
                    </el-button>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, toRefs, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { ElMessageBox, ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import CustomTable from '@/components/CustomTable.vue'
import PageResource from '@/api/page'
import UploadSingle from '@/components/images/UploadSingle.vue' // новый компонент загрузки
import HtmlEditor from '@/components/Editor/HtmlEditor.vue' // импорт редактора
import KeyValueFields from '@/components/Editor/KeyValueFields.vue'

const activeTab = ref('main')

const { t } = useI18n()
const router = useRouter()
const pageResource = new PageResource()

const form = reactive({
    name: '',
    customFields: []
})

function submitForm() {
    console.log(form)
}

// Основное состояние компонента
const state = reactive({
    tableData: [], // Данные таблицы
    loading: false, // Индикатор загрузки
    pagination: {
        total: 0,
        currentPage: 1,
        pageSize: 10,
    },
    params: {
        page: 1,
        per_page: 10,
        keyword: '',
    },
    pageSizes: [10, 30, 50, 100],
    dialogFormVisible: false, // Показывать ли форму создания
    creating: false, // Индикатор создания
    newPage: { // Модель новой страницы
        name: '',
        title: '',
        slug: '',
        content: '',
        image: '' // поле для URL загруженного изображения
    },
    rules: { // Валидация формы
        title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
        slug: [{ required: true, message: 'Slug is required', trigger: 'blur' }],
    }
})

// Определение колонок таблицы
const tableColumns = [
    { prop: 'id', label: 'ID', width: '80' },
    { prop: 'name', label: 'Name' },
    { prop: 'title', label: 'Title' },
    { prop: 'slug', label: 'Slug' },
    { prop: 'updated_at', label: 'Last Updated' },
]

// Опции действий в таблице
const tableOption = {
    label: t('table.actions'),
    fixed: 'right',
    item_actions: [
        { name: 'edit', type: 'primary', icon: 'EditPen' },
        { name: 'delete', type: 'danger', icon: 'Delete' },
    ]
}

// Получение списка страниц
const getList = async () => {
    state.loading = true
    try {
        const res = await pageResource.list(state.params)
        state.tableData = res.data
        state.pagination = res.meta
    } catch (error) {
        console.error(error)
    } finally {
        state.loading = false
    }
}

// Установка параметров фильтрации/пагинации
const setParams = (key, value) => {
    if (key !== 'per_page' && key !== 'page') {
        state.params.page = 1
    }
    state.params[key] = value
    getList()
}

// Обработка действий таблицы (редактировать / удалить)
const tableActions = (action, row) => {
    if (action === 'edit') {
        router.push(`/administrator/pages/edit/${row.id}`)
    } else if (action === 'delete') {
        ElMessageBox.confirm(`Delete page "${row.title}"?`, 'Warning', {
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            type: 'warning'
        }).then(() => {
            pageResource.destroy(row.id).then(() => {
                ElMessage.success('Page deleted')
                getList()
            })
        }).catch(() => {
            ElMessage.info('Delete canceled')
        })
    }
}

// Открытие диалога создания
const handleCreate = () => {
    state.dialogFormVisible = true
    state.newPage = {
        name: '',
        title: '',
        slug: '',
        content: '',
        image: ''
    }
}

// Создание новой страницы через API
const createPage = (formRef) => {
    if (!formRef) return
    formRef.validate(async (valid) => {
        if (!valid) return
        state.creating = true
        try {
            await pageResource.store(state.newPage)
            ElMessage.success(t('page.created'))
            state.dialogFormVisible = false
            getList()
        } catch (err) {
            console.error(err)
        } finally {
            state.creating = false
        }
    })
}


// Функция генерации slug
// Функция для slugify с транслитерацией русских букв в латиницу
const slugify = (text) => {
    if (!text) return ''

    const ruToLatMap = {
        а:'a', б:'b', в:'v', г:'g', д:'d', е:'e', ё:'yo', ж:'zh', з:'z', и:'i',
        й:'y', к:'k', л:'l', м:'m', н:'n', о:'o', п:'p', р:'r', с:'s', т:'t',
        у:'u', ф:'f', х:'h', ц:'c', ч:'ch', ш:'sh', щ:'shch', ъ:'', ы:'y', ь:'',
        э:'e', ю:'yu', я:'ya'
    }

    return text
        .toLowerCase()
        .split('')
        .map(char => ruToLatMap[char] ?? char)
        .join('')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
}

// Состояние, чтобы отслеживать, редактировал ли пользователь slug вручную
const slugEditedManually = ref(false)

// Следим за тем, редактировал ли пользователь slug вручную
watch(() => state.newPage.slug, (newSlug, oldSlug) => {
    // Если slug был изменён вручную (НЕ автогенерация), помечаем это
    if (newSlug !== slugify(state.newPage.name)) {
        slugEditedManually.value = true
    }
})

// Следим за изменением name и обновляем slug, если пользователь не редактировал вручную
watch(() => state.newPage.name, (newName) => {
    if (!slugEditedManually.value) {
        state.newPage.slug = slugify(newName)
    }
})

// Загрузка списка при монтировании компонента
onMounted(getList)

// Привязка отдельных значений для удобства
const { tableData, loading, pagination, params, pageSizes, dialogFormVisible, newPage, creating, rules } = toRefs(state)
const refPageForm = ref(null)
</script>

<style scoped>
.app-container {
    flex: 1;
    padding: 16px;
}
.filter-container {
    margin-bottom: 16px;
}
.dialog-footer {
    text-align: right;
    margin-top: 20px;
}
.custom-upload {
    width: 30px; /* ширина карточки загрузки */
    height: 30px; /* высота карточки загрузки */
}

.custom-upload .el-upload {
    width: 100%;
    height: 100%;
}

.custom-upload .el-upload--picture-card {
    width: 100%;
    height: 100%;
    line-height: 160px; /* чтобы иконка была по центру */
    text-align: center;
}
</style>
