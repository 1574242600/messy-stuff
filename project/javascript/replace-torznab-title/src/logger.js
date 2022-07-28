import winston from "winston";

const { format } = winston;
const logger = winston.createLogger({
    format: format.combine(format.timestamp(), format.cli()),
    level: "info",
    transports: [
        new winston.transports.Console({
            level: process.env.RTT_DEBUG ? "debug" : "info",
        }),
    ],
});

export default logger;
