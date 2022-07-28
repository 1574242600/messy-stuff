import * as cheerio from "cheerio";

//$1 发布组; $2,$3 中文标题,罗马音标题; $4$5$6 集数, 字幕语言, 画质 (顺序随意)
const animeReleaseTitleTemplate = "[$1] $2 / $3 [$4][$5][$6]"; 

function replaceAllTitle(xmlStr, rules) {
    const $ = cheerio.load(xmlStr, { xmlMode: true });
    
    $("title").each((_, el) => {
        el = $(el);
        el.text(replaceTitle(el.text(), rules));
    });
    
    return $.xml();
}

function replaceTitle(title, rules) {
    const rule = matchRule(rules, title);

    if (rule) {
        const { parse, template } = rule;
        return title.replace(new RegExp(parse), template ? template : animeReleaseTitleTemplate);
    }

    return title;
}

function matchRule(rules, title) {
    for (const ruleName in rules) {
        const rule = rules[ruleName];
        const is = (title) => (new RegExp(rule.is)).test(title);

        if (is(title)) {
            return rule;
        }
    }

    return null;
}

export default replaceAllTitle;