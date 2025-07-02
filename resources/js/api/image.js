import request from '@/utils/request'
import Resource from '@/api/resource'

class ImageResource extends Resource {
    constructor() {
        super('images') // /api/images
    }

    upload(formData) {
        return request({
            url: '/' + this.uri,
            method: 'post',
            data: formData,
        });
    }
}

export default ImageResource
