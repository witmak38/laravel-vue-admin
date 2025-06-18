import request from '@/utils/request';
import Resource from '@/api/resource';

class GalleryResource extends Resource {
    constructor() {
        super('gallery');
    }

    // Можно добавить специфичные методы, если понадобится
    // Например, получение статистики по фото или теги

    // Пример метода для загрузки по ID (если потребуется)
    show(id) {
        return request({
            url: '/' + this.uri + '/' + id,
            method: 'get'
        });
    }

    // Загрузка файла (с использованием multipart/form-data)
    upload(formData) {
        return request({
            url: '/' + this.uri,
            method: 'post',
            data: formData,
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
    }
}

export { GalleryResource as default };
