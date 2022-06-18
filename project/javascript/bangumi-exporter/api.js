import fetch from 'node-fetch';

class ApiRequest {
    origin;
    token;
    constructor(origin, token) {
        this.origin = origin;
        this.token = token;
    }

    async get(path, init = {}) {
        init = Object.assign({}, init, {
            method: 'GET'
        });

        return this._fetch(path, init);
    }

    async post(path, init = {}) {
        init = Object.assign({}, init, {
            method: 'POST',
        });

        return this._fetch(path, init);
    }

    async put(path, init = {}) {
        init = Object.assign({}, init, {
            method: 'PUT',
        });

        return this._fetch(path, init);
    }

    async _fetch(path, init = {}) {
        const url = this.origin + path;

        init = Object.assign({}, init, {
            headers: {
                'Authorization': 'Bearer ' + this.token,
                'Accept': 'application/json'
            }
        });

        const res = await fetch(url, init);

        if (!res.ok) {
            const info = 'API \n\n'
            + `url: ${ url } \n`
            + `status: ${ res.status } \n`
            + `fetch init: ${ JSON.stringify(init) } \n`;
            //+ `response: ${ await res.text() } \n`;

            throw new Error(info);
        }

        return res.json();
    }
}


class BGMApi {
    ApiRequest;
    constructor(origin, token) {
        this.ApiRequest = new ApiRequest(origin, token);
    }

    getMe() {
        return this._get('/v0/me')
    }

    getSubject(id) {
        return this._get(`/v0/subjects/${ id }`)
    }

    getUserCollections(userId, {
        subjectType,
        type,
        limit,
        offset
    }) { 
        const SearchParmas = new URLSearchParams()
        if (subjectType) SearchParmas.append('subject_type', subjectType);
        if (type) SearchParmas.append('type', type);
        if (limit) SearchParmas.append('limit', limit);
        if (offset) SearchParmas.append('offset', offset);

        const path = `/v0/users/${ userId }/collections?${ SearchParmas.toString() }`;
        return this._get(path)
    }
    
    _get(path, init = {}) {
        init.headers = Object.assign({}, init.headers, {
            'User-Agent': '1574242600/bangumi2myanimelist (https://github.com/1574242600/messy-stuff/tree/main/project/javascript/bangumi-exporter)',
        });
        
        return this.ApiRequest.get(path, init);
    }
}


class MALApi {
    ApiRequest;
    constructor(origin, token) {
        this.ApiRequest = new ApiRequest(origin, token);
    }

    getMyInfo() {
        return this.ApiRequest.get('/v2/user/@me')
    }

    getAnimeList(queryStr, {
        limit = 10,
        offset = 0,
        fields
    }) {
        const SearchParmas = new URLSearchParams();
        SearchParmas.append('q', queryStr);
        SearchParmas.append('limit', limit);
        SearchParmas.append('offset', offset);
        SearchParmas.append('nsfw', 'true');
        if (fields) SearchParmas.append('fields', fields.join(','));

        const path = `/v2/anime?${ SearchParmas.toString() }`;
        return this.ApiRequest.get(path);
    }

    updateAnimeListStatus(id, {
        status,
        score,
        numWatchedEpisodes
    }) {
        const SearchParmas = new URLSearchParams();
        if (status) SearchParmas.append('status', status);
        if (score) SearchParmas.append('score', score);
        if (numWatchedEpisodes) SearchParmas.append('num_watched_episodes', numWatchedEpisodes);
        
        const init = {
            body: SearchParmas,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        };

        return this.ApiRequest.put(`/v2/anime/${ id }/my_list_status`, init);
    }
}

export { BGMApi, MALApi };
