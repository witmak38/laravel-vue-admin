<template>
    <div class="app-container scroll-y">
        <div class="filter-container">
            <el-button class="filter-item" type="primary" :icon="Plus" @click="handleCreate">
                {{ t('table.add') }}
            </el-button>
        </div>

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

        <el-dialog :title="dialogTitle" v-model="dialogFormVisible" width="80%">
            <div v-loading="creating" class="form-container">
                <el-form ref="refPageForm" :model="newPage" :rules="rules" label-position="left" label-width="120px">
                    <el-tabs v-model="activeTab" tab-position="left" style="min-height: 400px;">
                        <el-tab-pane :label="t('office.main')" name="main">
                            <el-container>
                                <el-header>
                                    <h2>{{ t('office.h2') }}</h2>
                                </el-header>
                                <el-container>

                                    <el-container>
                                        <el-main>
                                            <el-form-item :label="t('office.name')" prop="name">
                                                <el-input v-model="newPage.name" />
                                            </el-form-item>
                                            <!-- keywords, если нужны, добавь сюда -->
                                        </el-main>
                                        <el-main>
                                            <el-form-item :label="t('office.addres')" prop="addres">
                                                <el-input v-model="newPage.addres" />
                                            </el-form-item>
                                            <el-form-item :label="t('office.phone')" prop="phone">
                                                <el-input v-model="newPage.phone" />
                                            </el-form-item>
                                            <el-form-item :label="t('office.worktime')" prop="worktime">
                                                <el-input v-model="newPage.worktime" />
                                            </el-form-item>

                                            <!-- keywords, если нужны, добавь сюда -->
                                        </el-main>
                                    </el-container>
                                </el-container>
                            </el-container>

                            <el-container>
                                <el-main>
                                    <el-form-item :label="t('office.content')">
                                        <html-editor v-model:content="newPage.content" />
                                    </el-form-item>
                                </el-main>
                            </el-container>
                        </el-tab-pane>
                    </el-tabs>
                </el-form>

                <div class="dialog-footer" style="margin-top: 20px;">
                    <el-button @click="dialogFormVisible = false">{{ t('table.cancel') }}</el-button>
                    <el-button type="primary" @click="createOrUpdatePage(refPageForm)">
                        {{ page?.id ? t('table.update') : t('table.confirm') }}
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
import HtmlEditor from '@/components/Editor/HtmlEditor.vue'
import ImageUploader from '@/components/Image/ImageUploader.vue'
import { debounce } from 'lodash-es'

const activeTab = ref('main')

const { t } = useI18n()
const router = useRouter()
const pageResource = new PageResource()

const state = reactive({
    tableData: [],
    loading: false,
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
    dialogFormVisible: false,
    creating: false,
    newPage: {
        name: '',
        meta_title: '',
        slug: '',
        content: '',
        image: '',
        meta_description: '',
        keywords: ''
    },
    rules: {
        meta_title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
        slug: [{ required: true, message: 'Slug is required', trigger: 'blur' }],
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }]
    }
})

const page = ref(null) // текущая редактируемая страница (или null)

const createDraftDebounced = debounce(async (name, slug) => {
  if (!name || !slug || page.value?.id || !state.dialogFormVisible) return
  try {
    const draft = await pageResource.createDraft({ name, slug })
    if (!page.value?.id) {
      page.value = draft
      Object.assign(state.newPage, draft)
    }
  } catch (err) {
    console.warn('Ошибка при создании черновика страницы:', err)
  }
}, 700)

watch(
  () => [state.newPage.name, state.newPage.slug],
  ([name, slug]) => {
    createDraftDebounced(name, slug)
  },
  { immediate: false }
)

// Таблица колонок и действий
const tableColumns = [
    { prop: 'id', label: 'ID', width: '80' },
    { prop: 'name', label: 'Name' },
    { prop: 'meta_title', label: 'Title' },
    { prop: 'slug', label: 'Slug' },
    { prop: 'updated_at', label: 'Last Updated' }
]

const tableOption = {
    label: t('table.actions'),
    fixed: 'right',
    item_actions: [
        { name: 'edit', type: 'primary', icon: 'EditPen' },
        { name: 'delete', type: 'danger', icon: 'Delete' }
    ]
}

// Получить список страниц
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

// Установка параметров фильтра/пагинации
const setParams = (key, value) => {
    if (key !== 'per_page' && key !== 'page') {
        state.params.page = 1
    }
    state.params[key] = value
    getList()
}

// Обработка действий таблицы
const tableActions = (action, row) => {
    if (action === 'edit') {
        handleEdit(row.id)
    } else if (action === 'delete') {
        ElMessageBox.confirm(`Delete page "${row.name}"?`, 'Warning', {
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

// Открыть диалог создания
const handleCreate = () => {
    resetForm()
    page.value = null // сброс черновика при открытии диалога создания
    state.dialogFormVisible = true
}

// Открыть диалог редактирования
const handleEdit = async (id) => {
    resetForm()
    state.dialogFormVisible = true
    try {
        const res = await pageResource.show(id)

        const normalizedImages = (res.images ?? []).map(img => {
            let normalizedPath = img.path
            if (!img.path.startsWith('/storage')) {
                normalizedPath = '/storage/' + img.path.replace(/^public\//, '')
            }

            return {
                id: img.id,
                url: window.location.origin + normalizedPath,
                title: img.title || '',
                alt: img.alt || '',
                sort_order: img.sort_order || 0,
            }
        })

        console.log('Final normalizedImages:', normalizedImages)

        page.value = {
            ...res,
            images: normalizedImages
        }

        // Обновляем модель формы с данными страницы
        Object.assign(state.newPage, {
            name: res.name,
            meta_title: res.meta_title,
            slug: res.slug,
            content: res.content,
            meta_description: res.meta_description,
            keywords: res.keywords || '',
            image: res.image || '',  // если есть отдельное поле
        })
    } catch (err) {
        console.error('Error loading page images:', err)
        ElMessage.error('Ошибка загрузки страницы')
    }
}

// Сбросить форму и состояние страницы
function resetForm() {
    page.value = null
    Object.assign(state.newPage, {
        name: '',
        meta_title: '',
        slug: '',
        content: '',
        image: '',
        meta_description: '',
        keywords: ''
    })
}

// Создать или обновить страницу
const createOrUpdatePage = (formRef) => {
    if (!formRef) return
    formRef.validate(async (valid) => {
        if (!valid) return
        state.creating = true
        try {
            if (page.value?.id) {
                await pageResource.update(page.value.id, { ...state.newPage })
                ElMessage.success(t('page.updated'))
            } else {
                await pageResource.store(state.newPage)
                ElMessage.success(t('page.created'))
            }
            state.dialogFormVisible = false
            getList()
            resetForm()
        } catch (err) {
            console.error(err)
            ElMessage.error('Ошибка сохранения')
        } finally {
            state.creating = false
        }
    })
}

// Отслеживаем редактирование slug вручную и автогенерацию
const slugEditedManually = ref(false)
watch(() => state.newPage.slug, (newSlug) => {
    if (newSlug !== slugify(state.newPage.name)) slugEditedManually.value = true
    else slugEditedManually.value = false
})
watch(() => state.newPage.name, (newName) => {
    if (!slugEditedManually.value) state.newPage.slug = slugify(newName)
})

// Сгенерировать slug
const slugify = (text) => {
    if (!text) return ''
    const ruToLatMap = {
        а: 'a', б: 'b', в: 'v', г: 'g', д: 'd', е: 'e', ё: 'yo', ж: 'zh', з: 'z', и: 'i',
        й: 'y', к: 'k', л: 'l', м: 'm', н: 'n', о: 'o', п: 'p', р: 'r', с: 's', т: 't',
        у: 'u', ф: 'f', х: 'h', ц: 'c', ч: 'ch', ш: 'sh', щ: 'shch', ъ: '', ы: 'y', ь: '',
        э: 'e', ю: 'yu', я: 'ya'
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
