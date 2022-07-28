import http from "http";
import logger from "./logger.js";
import defaultHandler from "./controller/default.js";


const server = http.createServer(async (req, res) => {
    logger.info(`${req.method} ${req.url}`);
    
    try {
        await defaultHandler(req, res);
    } catch (e) {
        logger.error(e);
        res.writeHead(500, { "Content-Type": "text/plain" });
        res.end("Internal Server Error");
    }
    
});

export default server;