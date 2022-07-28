FROM node:lts-alpine

ENV NODE_ENV=production

COPY ./src /app/src
COPY ./package.json /app/package.json

RUN cd /app \
    && npm install

WORKDIR /app
VOLUME /app/config/

EXPOSE 7724

ENTRYPOINT ["npm", "start", "--"]
CMD ["/app/config/config.yml"]