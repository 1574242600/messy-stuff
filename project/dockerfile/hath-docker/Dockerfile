FROM alpine
ENV HATH_VERSION="1.6.1"
ENV HATH_ZIP_NAME="HentaiAtHome_${HATH_VERSION}.zip" \
    HATH_ZIP_SHA256="b8889b2c35593004be061064fcb6d690ff8cbda9564d89f706f7e3ceaf828726"

WORKDIR /hath

COPY start.sh ./

RUN apk upgrade \
    && apk add wget unzip openjdk8-jre sqlite \
    && wget "https://repo.e-hentai.org/hath/${HATH_ZIP_NAME}" \
    && [ "$HATH_ZIP_SHA256" == $( sha256sum $HATH_ZIP_NAME  | cut -c 1-64 ) ] \
    && unzip $HATH_ZIP_NAME \
    && rm -f ./$HATH_ZIP_NAME

VOLUME /hath/download
VOLUME /hath/cache
VOLUME /hath/tmp
VOLUME /hath/log

EXPOSE 443

ENTRYPOINT [ "sh", "./start.sh"]
