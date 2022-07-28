import * as fs from "fs";
import replaceAllTitle from "../replace";

const testXml = fs.readFileSync("src/utils/__test__/xml/jashin-chan.xml", "utf8");

test("replaceAllTitle", () => {
    const result = replaceAllTitle(testXml, {
        rule1: {
            is: "幻櫻字幕組|幻樱字幕组",
            parse: "【(.*?)】【.*?】【(.*?)\\s(.*?)】【([0-9]*?)】【(\\w*?)】【(\\w*?)】",
        },
    });

    expect(result).toMatchSnapshot();
});