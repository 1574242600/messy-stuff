import http from "http";
import server from "../server";

describe("server", () => {
    let s;

    // test("listening", () => {
    //    expect(s.listening).toBe(true);
    //});

    test("request", (done) => {
        const req = http.request({
            port: 7724,
            host: "127.0.0.1",
        });
        req.on("response", (res) => res.statusCode === 200 && done());
        req.end();
    });


    beforeAll(() => {
        s = server.listen({
            port: 7724,
            host: "127.0.0.1",
        });
    });
});
