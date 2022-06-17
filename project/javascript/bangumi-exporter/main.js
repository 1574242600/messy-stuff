import { MALApi, BGMApi} from './api.js';


const BGM_ORIGIN = 'https://api.bgm.tv'
const MAL_ORIGIN = 'https://api.myanimelist.net'

const BGM_TOKEN = '';
const MAL_TOKEN = '';
const BGM_USER_ID = '';



(async function main() {
    const Bgm = new BGMApi(BGM_ORIGIN, BGM_TOKEN);
    const Mal = new MALApi(MAL_ORIGIN, MAL_TOKEN);
   
    const b_UserAnimes = await b_GetUserAllAnimes(Bgm, BGM_USER_ID);
    
    const notFound = [];
    for (const { BGMId, type, epStatus, rate } of b_UserAnimes) {

        const MALId = await toMALId(Bgm, Mal, BGMId);

        if (MALId) {
            await importAnime(Mal, MALId, type, { 
                b_EpStatus: epStatus, 
                b_Rate: rate
            });

            console.log(`BGMId: ${BGMId} -> MALId: ${MALId} Successful`);
        } else {
            notFound.push(BGMId);
            console.log(`BGMId: ${BGMId} not found in MAL`);
        }
    }

    if (notFound.length !== 0) console.log(`Not found: ${notFound.join(', ')}`);
})();

async function b_GetUserAllAnimes(BGMApi, userId) {
    const items = [];

    for (let i = 0; true; i++) {
        const { total, data } = await BGMApi.getUserCollections(userId, {
            subjectType: 2,
            limit: 100,
            offset: i * 100
        });

        items.push(...data);
        if ((i + 1) * 100 >= total) break;
    }

    return items
        .map(({ subject_id, type, ep_status, rate }) => { 
            return { 
                BGMId: subject_id, 
                type, 
                epStatus: ep_status, 
                rate 
            } 
        });
}

async function toMALId(BGMapi, MALApi, BGMId) {
    function check(animeDetails, b_SubjectInfo) {
        let scores = 0;

        const { alternative_titles, start_date, num_episodes } = animeDetails;
        const { name, date, eps } = b_SubjectInfo;

        //有些番在 BGM 字之间没有空格而在 MAL 有空格
        const b_Name = name.replace(/\s*/g,"");
        if (b_Name === alternative_titles.ja.replace(/\s*/g,"")
            || b_Name === alternative_titles.en.replace(/\s*/g,"")
        ) scores += 0.25;
        
        if (eps === num_episodes) scores += 0.25;

        //不知道为什么, MAL上的日期有时比 BGM 慢一天
        const dateDiff = Math.abs(new Date(start_date).getTime() - new Date(date).getTime());
        if (dateDiff <= 86400000) scores += 0.5;

        return scores >= 0.5;
    }


    const b_SubjectInfo = await BGMapi.getSubject(BGMId);

    //查询字符串必须两位以上
    let fillFlag = false;
    if (b_SubjectInfo.name.length <= 2) { 
        b_SubjectInfo.name += '**'
        fillFlag = true;
    };
    const { data } = await MALApi.getAnimeList(b_SubjectInfo.name, { 
        limit: 10, 
        fields: [ 
            'id',
            'alternative_titles',
            'start_date',
            'num_episodes'
        ] 
    });
    if (fillFlag) 
        b_SubjectInfo.name = b_SubjectInfo.name.slice(0, -2);


    for (const { node } of data) {
        if (check(node, b_SubjectInfo)) {
            return node.id;
        }
    }

    return null;
}

async function importAnime(MALApi, MALId, b_Type, { 
    b_EpStatus,
    b_Rate,
}) {
    function toMALStatus(b_type) {
        const statusMap = [
            'plan_to_watch',
            'completed',
            'watching',
            'on_hold',
            'dropped'
        ]

        return statusMap[b_type - 1];
    }
    
    await MALApi.updateAnimeListStatus(MALId, {
        status: toMALStatus(b_Type),
        score: b_Rate,
        numWatchedEpisodes: b_EpStatus
    });
}
