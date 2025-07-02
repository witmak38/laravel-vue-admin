import request from '@/utils/request';
import Resource from '@/api/resource';

class PageResource extends Resource {
    constructor() {
        super('pages'); // Laravel route: /api/pages
    }

    // Получить страницу по ID
    show(id) {
        return request({
            url: '/' + this.uri + '/' + id,
            method: 'get',
        });
    }

    getImages(id) {
        return request({
            url: '/pages/' + id,
            method: 'get',
        });
    }

    // Обновить страницу по ID
    update(id, data) {
        return request({
            url: '/' + this.uri + '/' + id,
            method: 'put',
            data,
        });
    }

    // Получить страницу по slug
    bySlug(slug) {
        return request({
            url: '/' + this.uri + '/slug/' + slug,
            method: 'get',
        });
    }

    // Обновить SEO-мета (дополнительно)
    updateMeta(id, meta) {
        return request({
            url: '/' + this.uri + '/' + id + '/meta',
            method: 'put',
            data: meta,
        });
    }

    // Создать черновик
    createDraft(payload) {
        return request({
            url: '/pages/draft',
            method: 'post',
            data: payload,
        });
    }
}

export { PageResource as default };
