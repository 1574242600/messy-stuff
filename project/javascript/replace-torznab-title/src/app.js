#!/usr/bin/env node
import { config, loadConfig } from "./config.js";
import logger from "./logger.js";
import server from "./server.js";

(async function main(argv) {
    const configFilePath = argv[2];
    loadConfig(configFilePath);

    const { port, host } = config;

    server.listen({ port, host });
    logger.info(`Server started on ${host}:${port}`);
    
})(process.argv);

process.on("SIGINT", () => {
    logger.info("SIGINT received");
    if (!server.listening) process.exit(0);
    server.close(() => process.exit(0));
});

process.on("uncaughtException", e => {
    logger.error(e.stack);
    process.exit(1);
});

process.on("unhandledRejection", e => {
    logger.error(e.stack);
    process.exit(1);
});