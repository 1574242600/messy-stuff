import * as fs from "fs";
import yaml from "js-yaml";
import { z } from "zod";

const { record, object, number, string } = z;
const configSchema = object({
    port: number().default(7724),
    host: string().default("127.0.0.1"),
    rules: record(
        object({
            url: string().url(),
            repRule: record(
                object({
                    is: string(),
                    parse: string(),
                    template: string().optional(),
                })
            ),
        })
    ),
});

let config;

function loadConfig(path) {
    try {
        const str = fs.readFileSync(path, "utf8");
        config = yaml.load(str);
        config = configSchema.parse(config);
    } catch (e) {
        throw new Error(`Failed to load config file: ${e.message}`);
    }
}

export { config, loadConfig };