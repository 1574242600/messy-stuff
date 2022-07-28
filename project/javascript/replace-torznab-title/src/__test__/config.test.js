import { loadConfig, config } from "../config";

test("loadConfig", () => {
    loadConfig("src/__test__/config/1.yml");
    
    expect(config).toMatchSnapshot();
});