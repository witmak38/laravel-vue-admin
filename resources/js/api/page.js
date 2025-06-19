import request from '@/utils/request';
import Resource from '@/api/resource';

class PageResource extends Resource {
    constructor() {
        super('pages'); // Laravel route: /api/pages
    }

    // Если понадобится получить одну страницу по slug:
    bySlug(slug) {
        return request({
            url: '/' + this.uri + '/slug/' + slug,
            method: 'get',
        });
    }

    // Пример обновления SEO-мета (дополнительно):
    updateMeta(id, meta) {
        return request({
            url: '/' + this.uri + '/' + id + '/meta',
            method: 'put',
            data: meta,
        });
    }
}

export { PageResource as default };
