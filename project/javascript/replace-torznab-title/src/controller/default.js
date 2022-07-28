import { config } from "../config.js";
import fetch from "node-fetch";
import replaceAllTitle from "../utils/replace.js";

async function handler(req, res) {
    const { rules } = config || {};
    const url = new URL(req.url, `http://${req.headers.host}`);
    const name = url.pathname.split("/")[0];

    const rule = rules[name] || null;

    if (rule) {
        const { url, repRule } = rule;

        const xmlStr = await fetch(url + url.search)
            .then(r => r.text());
        
        res.writeHead(200, { "Content-Type": "application/xml" });
        res.end(replaceAllTitle(xmlStr, repRule));
    } else {
        res.writeHead(404, { "Content-Type": "text/plain" });
        res.end("Not Found");
    }
}

export default handler;